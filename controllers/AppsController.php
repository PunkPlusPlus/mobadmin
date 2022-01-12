<?php

namespace app\controllers;

use app\models\Namings;
use Yii;
use app\models\Apps;
use app\models\AppsSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Zones;
use app\components\AppsHelper;
use Raulr\GooglePlayScraper\Scraper;
use yii\base\ErrorException;

use app\models\Users;
use app\models\Linkcountries;
use app\models\Devices;
use app\models\Visits;
use app\models\AppsAccess;
use app\models\Prices;
use app\models\Links;
use app\models\Params;
use webvimark\modules\UserManagement\models\User;
use app\basic\debugHelper;

use app\controllers\MediaController;
use app\controllers\LinkcountriesController;
use app\controllers\GpscraperController;
/**
 *
 * AppsController implements the CRUD actions for Apps model.
 */
class AppsController extends BaseAdminController
{

    /**
     * Lists all Apps models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AppsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            //'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOpenNaming($app_id, $url, $user_price)
    {
        $value = null;
        $app = Apps::findOne($app_id);
        $user = User::findOne($user_price);
        $result = Namings::createNamingLink($user, $url, $value, $app);
        $logger = new LogsController();
        $logger->data['message'] = $result;
        $logger->infoSend("OpenNaming");
        return $this->redirect(['apps/view', 'id' => $app_id]);
    }

    public function actionDeleteAll($app_id)
    {
        $app = Apps::findOne($app_id);
        if (!$app) $error_log = "No app";
        $zones = Zones::getZones();
        $data['geo'] = $zones;
        $data['package'] = $app->package;
        //$data['url'] = $url;
        $user = User::getCurrentUser();
        $result = AppsHelper::deleteAll($data);
        return $this->redirect(['apps/view', 'id' => $app_id]);
    }

    public function actionOpenAll($app_id, $url)
    {
        $error_log = '';
        $app = Apps::findOne($app_id);
        if (!$app) $error_log = "No app";
        $zones = Zones::getZones();
        $data['geo'] = $zones;
        $data['package'] = $app->package;
        $data['url'] = $url;
        $user = User::getCurrentUser();
        $result = AppsHelper::createLink($data, $user, 1);
        var_dump($result);
        return $this->redirect(['apps/view', 'id' => $app_id]);
    }

    /**
     * Displays a single Apps model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        if(!User::hasPermission('view_all_apps')) {

            $connection = Yii::$app->getDb();
            $accessApp = $connection->createCommand("
                SELECT
                tbl_links.user_id,
                tbl_linkcountries.app_id
                FROM
                tbl_links
                INNER JOIN tbl_linkcountries ON tbl_linkcountries.id = tbl_links.linkcountry_id
                WHERE tbl_links.user_id = :user_id
                AND tbl_linkcountries.app_id = :app_id
            ", [':user_id' => User::getCurrentUser()->id, ':app_id' => $id]);
            $accessApp = $accessApp->queryAll();

            if(!$accessApp) {
                header('HTTP/1.0 403 Forbidden');
                echo '403 Forbidden';
                exit();
            }

        }
		$model = $this->findModel($id);
		
		$country = $_GET['country'] ?? 'all';
		$countryScrapper = $country;
		if($countryScrapper == 'all')
			$countryScrapper = 'ru';

        $app = GpscraperController::getInfo($model->package);
        
		$allUsers = ArrayHelper::map(Users::find()->all(), 'id', 'display_name');

        $listCountryLinks = [];

        $countryAll = Linkcountries::find()
            ->where(['app_id' => $id])
            ->andWhere(['archived' => 0])
            ->all();

		foreach($countryAll as $value){
		    $dublicate = [
		        "id" => $value->id,
		        "country_code" => $value->country_code,
                "extra" => $value->extra,
                "users" => [],
            ];

		    $allLinks = $value->activelinks;

		    foreach($allLinks as $link){
                //назначаем имена для партеров
                foreach($allUsers as $userId => $userData){
                    if($userId == $link->user_id){
                        $block = false;
                        foreach ($dublicate['users'] as $item){
                            if($item['id'] == $userId){
                                $block = true;
                            }
                        }
                        if(!$block) {
                            if(User::hasPermission('view_all_apps')) {
                                $userInfo = [
                                    "id" => $userId,
                                    "name" => $allUsers[$userId]
                                ];
                                array_push($dublicate['users'], $userInfo);
                            }else{
                                if($link->user_id == User::getCurrentUser()->id) {
                                    $userInfo = [
                                        "id" => $userId,
                                        "name" => $allUsers[$userId]
                                    ];
                                    array_push($dublicate['users'], $userInfo);
                                }
                            }
                        }
                    }
                }
            }
            array_push($listCountryLinks, $dublicate);
		}

		//$allVisits = $this->getAllVisits($id, $country);
		$allVisits = [];
        $visits = Visits::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(15);

        if(isset($_GET['country']) && $_GET['country'] != 'all') {
            $visits->andOnCondition(['country_code' => $_GET['country']]);
        }
        
        $blockInfo = false;
        if($model['published'] == -1){
            $blockInfo = true;
        }
        if(User::hasPermission('edit_all_apps') || User::hasRole('Developer')){
            $blockInfo = false;
        }

        return $this->render('view', [
            'model' => $model,
			'googlePlay' => $app,
			'listCountryLinks' => $listCountryLinks,
            'blockInfo' => $blockInfo
        ]);
    }
	
	public function getAllVisits($id, $country){
	
		if($country == 'all'){
			$allVisits = Visits::find()
				->where(['app_id' => $id])
				->orderBy(['id' => SORT_DESC])
				->limit(15)
				->all();
		}else{
			$allVisits = Visits::find()
				->where(['app_id' => $id])
				->andWhere(['country_code' => $country])
				->orderBy(['id' => SORT_DESC])
				->limit(15)
				->all();
		}
		return $allVisits;
	}
	
    /**
     * Creates a new Apps model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Apps();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $country_code = "all";
            $app_id = $model->id;
            $url = "/";
            LinkcountriesController::createOne($country_code, $app_id);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Apps model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
   public function actionUpdate($id)
   {
	    if(!User::hasPermission('edit_all_apps')) {
            header('HTTP/1.0 403 Forbidden');
            echo '403 Forbidden';
            exit();
        }
        $statuses = [
            '-1' => 'Banned',
            '0' => 'No_published',
            '1' => 'Published',
            '2' => 'Pending',
            '3' => 'Testing',
            '4' => 'Ready',
            '5' => 'Revision',
        ];

        $model = $this->findModel($id);
        $user = User::getCurrentUser();
        
        $status = $model->published;

        $logger = new LogsController();
        $logger->data['message']['app']['package'] = $model->package;
        $logger->data['message']['app']['name'] = $model->name;
        $logger->data['message']['user']['name'] = $user->username;
        $logger->data['message']['user']['id'] = $user->id;


        if ($model->load(Yii::$app->request->post())) {
            if ($status != $model->published || isset($_POST['ready'])) {
                if (isset($_POST['ready'])) {
                    $model->published = 4;
                    $model->save();
                    $logger->data['message']['event']['name'] = 'App_ready';
                    $logger->data['message']['event']['time'] = date("Y-m-d H:i:s");
                    $logger->infoSend('Event');
                } else {
                    $logger->data['message']['event']['name'] = $statuses[$model->published];
                    $logger->data['message']['event']['time'] = date("Y-m-d H:i:s");
                    $logger->infoSend('Event');
                }
                switch ($model->published) {
                    case -1:
                        $model->banned_time = date("Y-m-d H:i:s");
                        break;
                    case 3:
                        $model->testing_time = date("Y-m-d H:i:s");
                        break;
                    case 4:
                        $model->ready_time = date("Y-m-d H:i:s");
                        break;
                    case 5:
                        $model->revision_time = date("Y-m-d H:i:s");
                        break;
                }
                $model->save();
            }
            

            if(strlen($_POST['prices']) > 3){
                $errorPrices = json_encode($this->addPrices($model->id, $_POST['prices']));
            }else{
                $errorPrices = false;
            }

            if(strlen($_POST['params']) > 3){
                $errorParams = json_encode($this->addParams($model->id, $_POST['params']));
            }else{
                $errorParams = false;
            }

            if (!$errorParams && !$errorPrices && $model->save()) {
                //return $this->redirect(['view', 'id' => $model->id]);
            }
        }


        $prices = Prices::find()
            ->where(['app_id' => $model->id])
            ->all();


        $params = Params::find()
            ->where(['app_id' => $model->id])
            ->all();

        return $this->render('update', [
            'model' => $model,
            'prices' => $prices,
            'params' => $params
        ]);
    }

    public function addParams($appId, $jsonParams){
        $params = json_decode($jsonParams, true);
        for($i=0; $i<count($params); $i++){
            $params[$i]['app_id'] = $appId;
        }
        $errorParams = $params;

        for($i=0; $i<count($params); $i++){
            $user_id = $params[$i]['user_id'];
            $country_id = $params[$i]['country_id'];
            $key = $params[$i]['key'];
            $value = $params[$i]['value'];
            $access_level = $params[$i]['access_level'];
            $is_for_bot = $params[$i]['is_for_bot'];
            $param_id = $params[$i]['param_id'] ?? false;

            if(!$param_id) {
                $param = Params::find()
                    ->where(['app_id' => $appId])
                    ->andWhere(['linkcountry_id' => $country_id])
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere(['key' => $key])
                    ->andWhere(['is_for_bot' => $is_for_bot])
                    ->one();
            }else{
                $param = Params::find()
                    ->where(['id' => $param_id])
                    ->one();
                //debugHelper::print($param);
            }
            if(!$param || $param_id) {
                foreach ($errorParams as $keyError => $valueError) {
                    if ($errorParams[$keyError]['country_id'] == $country_id
                        && $errorParams[$keyError]['user_id'] == $user_id
                        && $errorParams[$keyError]['app_id'] == $appId) {
                        unset($errorParams[$keyError]);
                    }
                }
            }
            if(!$param) {
                $param = new Params();
            } else {
                $old_param = clone $param;
            }
            $param->app_id = $appId;
            $param->user_id = $user_id;
            $param->linkcountry_id = $country_id;
            $param->key = $key;
            $param->value = $value;
            $param->access_level = $access_level;
            $param->is_for_bot = $is_for_bot;

            $logger = new LogsController();
            if(!isset($old_param)) {
                $logger->logCreateParam($param);
            } else {
                $logger->logUpdateParam($old_param, $param);
            }

            $param->save();


        }

        return $errorParams;
    }

    public function actionDeleteparam($id)
    {
        $param = Params::find()
            ->where(['id' => $id]);


        if ($param = $param->one()) {
            if ($param->delete()) {
                return $this->redirect(['apps/update', 'id' => $param->app_id]);
            } else {
                die("Ошибка удаления стоимости для страны");
            }
        } else {
            die("Запись не найдена");
        }
    }


    public function addPrices($appId, $jsonPrices){
        $prices = json_decode($jsonPrices, true);
        for($i=0; $i<count($prices); $i++){
            $prices[$i]['app_id'] = $appId;
        }
        $errorPrices = $prices;

        for($i=0; $i<count($prices); $i++){
            $user_id = $prices[$i]['user_id'];
            $country_code = $prices[$i]['country_code'];
            $amount = $prices[$i]['price'];
            $priceid = $prices[$i]['price_id'] ?? false;

            if(!$priceid) {
                $price = Prices::find()
                    ->where(['app_id' => $appId])
                    ->andWhere(['country_code' => $country_code])
                    ->andWhere(['user_id' => $user_id])
                    ->one();
            }else{
                $price = Prices::find()
                    ->where(['id' => $priceid])
                    ->one();
            }
            if(!$price || $priceid){
                foreach($errorPrices as $keyError=>$valueError){
                    if($errorPrices[$keyError]['country_code'] == $country_code
                        && $errorPrices[$keyError]['user_id'] == $user_id
                        && $errorPrices[$keyError]['app_id'] == $appId){
                        unset($errorPrices[$keyError]);
                    }
                }
                if(!$price) $price = new Prices();
                $price->user_id = $user_id;
                $price->country_code = $country_code;
                $price->app_id = $appId;
                $price->price = $amount;
                $price->save();

            }
        }
        return $errorPrices;
    }

    public function actionDeleteprice($id)
    {
        $price = Prices::find()
            ->where(['id' => $id]);


        if ($price = $price->one()) {
            if ($price->delete()) {
                return $this->redirect(['apps/update', 'id' => $price->app_id]);
            } else {
                die("Ошибка удаления стоимости для страны");
            }
        } else {
            die("Запись не найдена");
        }
    }

    /**
     * Deletes an existing Apps model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        if(!User::hasPermission('view_all_apps')) {
            $accessApp = AppsAccess::find()
                ->where(['app_id' => $id])
                ->andWhere(['user_id' => User::getCurrentUser()->id]);

            if (!$accessApp->one()) {
                header('HTTP/1.0 403 Forbidden');
                echo '403 Forbidden';
                exit();
            }
        }

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionChangeTraffic($app_id, $route_id){
        if(!User::hasRole(['Developer', 'support'])) {
            throw new MethodNotAllowedHttpException('Нет доступа' );
        }
        $app = Apps::findOne($app_id);
        $app->traffic_route = $route_id;
        $app->save();
        return $this->redirect(['apps/view', 'id' => $app_id]);
    }

    public function actionDownloadApk($id)
    {
        $path = Yii::getAlias('@apk') . '/';
        $app = $this->findModel($id);
        $apk = $path . $app->apk;

        if (file_exists($apk)) {
            return Yii::$app->response->sendFile($apk);
        }
        throw new \Exception('File not found');
    }

    public function actionDeleteApk($id)
    {
        $path = Yii::getAlias('@apk') . '/';
        $app = $this->findModel($id);
        $apk = $path . $app->apk;

        if (file_exists($apk)) {
            unlink($apk);
        }
        $app->apk = null;
        $app->save();
        return $this->redirect(['/apps/view', 'id' => $id]);
    }

    public function actionDownloadKeystore($id)
    {
        $path = Yii::getAlias('@keystore') . '/';
        $app = $this->findModel($id);
        $keystore = $path . $app->keystore;

        if (file_exists($keystore)) {
            return Yii::$app->response->sendFile($keystore);
        }
        throw new \Exception('File not found');
    }

    public function actionDeleteKeystore($id)
    {
        $path = Yii::getAlias('@keystore') . '/';
        $app = $this->findModel($id);
        $keystore = $path . $app->keystore;

        if (file_exists($keystore)) {
            unlink($keystore);
        }
        $app->keystore = null;
        $app->save();
        return $this->redirect(['/apps/view', 'id' => $id]);
    }

    /**
     * Finds the Apps model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Apps the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Apps::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDebug($id){

        if (!User::hasPermission('edit_all_apps')) {
            print "403 Forbidden";
            exit();
        }
        return $this->render('debug', [
            //'model' => $model,
        ]);
    }

    public function actionTest($id, $action){
        $result = [];
        switch ($action){
            case "main":
                $result = $this->mainTest($id);
                break;
            case "analyze":
                $result = $this->analyzeTest($id);
                break;
        }
        return json_encode($result);
    }

    function mainTest($appId){
        $result = [
            "prod_server" => [
                "name" => "Запрос на реальном сервере",
                "value" => false
            ],
        ];
        $connection = Yii::$app->getDb();
        $findLastVisits = $connection->createCommand("
            SELECT
                tbl_visits.id,
                tbl_visits.server_name,
                tbl_devices.app_id
            FROM
                tbl_visits
            INNER JOIN tbl_devices ON tbl_visits.device_id = tbl_devices.id
            WHERE
                tbl_devices.app_id = :app_id
            ORDER BY
                tbl_visits.id DESC
            LIMIT 10
        ", [':app_id' => $appId]);
        $findLastVisits = $findLastVisits->queryAll();

        if($findLastVisits[0]['server_name'] != "aflagroupdev.profitnetwork.app"){
            $result['prod_server']['value'] = true;
        }


        return $result;
    }

    function analyzeTest($appId){

        $result = [
            "geturl_none" => [
                "name" => "Процент запусков с отсутствующим вторым запросом",
                "value" => 0
            ],
        ];
        $connection = Yii::$app->getDb();
        $findCountNoneURL = $connection->createCommand("
            SELECT
                COUNT(tbl_visits.id) as count
            FROM
                tbl_visits
            INNER JOIN tbl_devices ON tbl_visits.device_id = tbl_devices.id
            WHERE
                tbl_visits.url IS NULL AND
                tbl_devices.app_id = :app_id
        ", [':app_id' => $appId]);
        $findCountNoneURL = $findCountNoneURL->queryOne();


        $connection = Yii::$app->getDb();
        $findCountValidURL = $connection->createCommand("
            SELECT
                COUNT(tbl_visits.id) as count
            FROM
                tbl_visits
            INNER JOIN tbl_devices ON tbl_visits.device_id = tbl_devices.id
            WHERE
                tbl_visits.url IS NOT NULL AND
                tbl_devices.app_id = :app_id
        ", [':app_id' => $appId]);
        $findCountValidURL = $findCountValidURL->queryOne();

        $onePercent = ($findCountNoneURL['count']+$findCountValidURL['count'])/100;
        //debugHelper::print($findCountNoneURL['count']." - ".$findCountValidURL['count']." - ".$onePercent." - ".$findCountNoneURL['count']/$onePercent);
        $result['geturl_none']['value'] = round($findCountNoneURL['count']/$onePercent, 1);
        return $result;
    }
}
