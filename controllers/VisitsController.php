<?php /** @noinspection ALL */

namespace app\controllers;

use app\models\Apps;
use app\models\Linkcountries;
use Yii;
use app\models\Links;
use app\models\Visits;
use app\models\VisitsSearch;
use app\models\Prices;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\basic\debugHelper;

use app\models\Devices;
use app\models\AppsAccess;
use webvimark\modules\UserManagement\models\User;

/**
 * VisitsController implements the CRUD actions for Visits model.
 */
class VisitsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'ghost-access' => [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }

    /**
     * Lists all Visits models.
     * @return mixed
     */
    public function actionIndex($app_id)
    {
        $from = $_GET['from'] ?? date("d/m/Y", strtotime('-1 months'));
        $to = $_GET['to'] ?? date("d/m/Y");
        $dateStartFormat = substr($from, 6, 4) . "-" . substr($from, 3, 2) . "-" . substr($from, 0, 2)." 00:00:00";
        $dateEndFormat = substr($to, 6, 4) . "-" . substr($to, 3, 2) . "-" . substr($to, 0, 2)." 23:59:59";

        if (isset($_GET['VisitsSearch']['linkcountry_id'])) {
            $linkId = $_GET['VisitsSearch']['linkcountry_id'];
        }

        $selectCountry = $_GET['linkcountry'] ?? false;
		$selectLinks = $_GET['links'] ?? false;
        $selectLabels = $_GET['labels'] ?? false;
        $selectUsers = $_GET['users'] ?? false;

        if($selectCountry) {
            $selectCountry = explode(",", $selectCountry);
        }
		
		if($selectLinks) {
            $selectLinks = explode(",", $selectLinks);
        }

        if($selectLabels) {
            $selectLabels = explode(",", $selectLabels);
            for($i=0; $i<count($selectLabels); $i++){
                $selectLabels[$i] = str_replace("otherlbl", "NULL", $selectLabels[$i]);
            }
        }

        if($selectUsers) {
            $selectUsers = explode(",", $selectUsers);
        }
		

        $appInfo = Apps::find()
            ->where(['=', 'id', $app_id])
            ->one();
        $appPrices = $appInfo->prices;

        $whereUser = "";
        $whereLinks = "";
        if (!User::hasPermission('view_all_apps')) {
            $linkCountries = Linkcountries::find()
                ->where(['=', 'app_id', $app_id])
                //->andWhere(['archived' => 0])
                ->all();

            $whereLink = ['OR'];
            foreach ($linkCountries as $value) {
                array_push($whereLink, ["linkcountry_id" => $value["id"]]);
            }

            $appsAccessList = Links::find();
            $appsAccessList->where($whereLink);
            if(!User::hasPermission('view_all_statistics')) {
                $appsAccessList->andWhere(['user_id' => User::getCurrentUser()->id]);
                $whereUser = " AND tbl_links.user_id = ".User::getCurrentUser()->id;
            }


            if (!$appsAccessList->one() || $app_id == -1) {
                print "403 Forbidden";
                exit();
            }
        }
        $whereCountry = "";
        if($selectCountry){
            $i = 0;
            $whereCountry = " AND (";
            foreach($selectCountry as $countryId) {
                if($i > 0){
                    $whereCountry .= " OR ";
                }
                $whereCountry .= "tbl_links.linkcountry_id = ".$countryId;
                $i++;
            }
            $whereCountry .= ")";
        }


        if($selectLabels){
            $i = 0;
            $whereLinks = " AND (";
            foreach($selectLabels as $label) {
                $label = Yii::$app->db->quoteValue($label);
                if($i > 0){
                    $whereLinks .= " OR ";
                }
                $whereLinks .= "tbl_links.label = ".$label;
                $i++;
            }
            $whereLinks .= ")";
        }

		//фильтр по linkId
		if($selectLinks){
            $i = 0;
            $whereLinks = " AND (";
            foreach($selectLinks as $link) {
                if($i > 0){
                    $whereLinks .= " OR ";
                }
                $whereLinks .= "tbl_links.id = ".$link;
                $i++;
            }
            $whereLinks .= ")";
        }
		//end фильтр по linkid


        //фильтр по UserId
        $whereUsers = "";
        if($selectUsers){
            $i = 0;
            $whereUsers = " AND (";
            foreach($selectUsers as $user) {
                if($i > 0){
                    $whereUsers .= " OR ";
                }
                $whereUsers .= "tbl_links.user_id = ".$user;
                $i++;
            }
            $whereUsers .= ")";
        }
        //end фильтр по UserId

        $allLinkCountries = Linkcountries::find()
            ->where(['=', 'app_id', $app_id])
            //->andWhere(['archived' => 0])
            ->all();

        $whereLink = ['OR'];
        foreach ($allLinkCountries as $value) {
            array_push($whereLink, ["linkcountry_id" => $value["id"]]);
        }
        $links = Links::find();
        $links->where($whereLink);
        if(!User::hasPermission('view_all_statistics')) {
            $links->andWhere(['user_id' => User::getCurrentUser()->id]);
        }
        $links = $links->all();

        $allLinkCountries = Linkcountries::find()
            ->where(['=', 'app_id', $app_id]);
            //->andWhere(['archived' => 0]);



        $where = ['OR'];
        foreach ($links as $value) {
            array_push($where, ["id" => $value["linkcountry_id"]]);
        }
        $allLinkCountries->andWhere($where);
        $allLinkCountries = $allLinkCountries->all();

        $searchModel = new VisitsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $app_id, $selectCountry, $selectLinks, $selectLabels, $selectUsers, $dateStartFormat, $dateEndFormat);

        $stats = [
            "visits" => 0,
            "devices" => 0,
            "bots_devices" => 0,
            "bots_visits" => 0,
            "countries" => 0
        ];

        $linkCountries = Linkcountries::find();
        //$linkCountries->where(['archived' => 0]); were
        $linkCountries->where(['app_id' => $app_id]);
        $linkCountries = $linkCountries->all();

        $whereLinks = str_replace(" = 'NULL'", " IS NULL", $whereLinks);
        $connection = Yii::$app->getDb();
        $visits = $connection->createCommand("
        SELECT
            tbl_visits.is_redirect
        FROM
            tbl_visits
            INNER JOIN tbl_links ON tbl_visits.link_id = tbl_links.id
            INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
        WHERE
            tbl_linkcountries.app_id = :app_id" .$whereUser.$whereLinks.$whereCountry.$whereUsers. "
            AND tbl_visits.date >= CAST(:date_start AS DATETIME)
            AND tbl_visits.date < CAST(:date_end AS DATETIME)
    ", [':app_id' => $app_id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);
	
        $visits = $visits->queryAll();

        $namingVisits = $connection->createCommand("
            SELECT 
                tbl_visits.is_redirect
            FROM
                tbl_visits
                INNER JOIN tbl_links ON tbl_visits.link_id = tbl_links.id
                INNER JOIN tbl_namings ON tbl_links.id = tbl_namings.link_id
            WHERE
                tbl_namings.app_id = :app_id
                AND tbl_visits.date >= CAST(:date_start AS DATETIME)
                AND tbl_visits.date < CAST(:date_end AS DATETIME)

        ", [':app_id' => $app_id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);
        $namingVisits = $namingVisits->queryAll();

	        $visits_redirect = 0;
        foreach ($visits as $visit) {
            if($visit['is_redirect'] == 1) $visits_redirect++;
            //if ($namingVisits['is_redirect'] == 1) $visits_redirect++;
        }
        foreach ($namingVisits as $visit) {
            if ($visit['is_redirect'] == 1) $visits_redirect++;
        }
        $visits = count($visits) + count($namingVisits);
	$test_devices = $connection->createCommand("
        SELECT
            Count( * ) AS count,
            tbl_linkcountries.app_id,
            tbl_links.linkcountry_id,
            tbl_linkcountries.country_code
        FROM
            tbl_linkcountries
            INNER JOIN tbl_links ON tbl_linkcountries.id = tbl_links.linkcountry_id
            INNER JOIN tbl_devices ON tbl_devices.link_id = tbl_links.id 
        WHERE
            tbl_linkcountries.app_id = :app_id ".$whereUser.$whereLinks.$whereCountry.$whereUsers."
        AND tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
        AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
        GROUP BY
            tbl_links.id
        ", [':app_id' => $app_id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);
        $test_devices = $test_devices->queryAll();
	//$logd = new LogsController();
	//$logd->data['message'] = json_encode($test_devices);
	//$logd->infoSend("VisitTest");
        $devices = $connection->createCommand("
        SELECT
            Count( * ) AS count,
            tbl_linkcountries.app_id,
            tbl_links.linkcountry_id,
            tbl_linkcountries.country_code
        FROM
            tbl_linkcountries
            INNER JOIN tbl_links ON tbl_linkcountries.id = tbl_links.linkcountry_id
            INNER JOIN tbl_devices ON tbl_devices.link_id = tbl_links.id 
        WHERE
            tbl_linkcountries.app_id = :app_id ".$whereUser.$whereLinks.$whereCountry.$whereUsers."
        AND tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
        AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
        GROUP BY
            tbl_links.id
        ", [':app_id' => $app_id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);

        $queryLinkString = "";
        foreach ($linkCountries as $link) {
            if (strlen($queryLinkString) > 0) {
                $queryLinkString .= " OR ";
            }
            $queryLinkString .= "tbl_visits.linkcountry_id = " . $link->id;
        }

        $devices = $devices->queryAll();

        $namingDevices = $connection->createCommand("
            SELECT
                Count( * ) AS count
            FROM
                tbl_devices
                INNER JOIN tbl_namings on tbl_devices.link_id = tbl_namings.link_id
                INNER JOIN tbl_links on tbl_devices.link_id = tbl_links.id
            WHERE
                tbl_namings.app_id = :app_id
            AND tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
            AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)    
        ", [':app_id' => $app_id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);
        $namingDevices = $namingDevices->queryAll();

	        //устанавливаем цену
        $connection = Yii::$app->getDb();
        if (!User::hasPermission('view_all_statistics')) {
            $apps = $connection->createCommand("
            SELECT
                Count( * ) AS installs,
                tbl_linkcountries.country_code,
                tbl_linkcountries.app_id,
                `user`.display_name,
                `user`.email,
                `user`.id as user_id
            FROM
                tbl_devices
                INNER JOIN tbl_links ON tbl_devices.link_id = tbl_links.id
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
                INNER JOIN `user` ON `user`.id = tbl_links.user_id 
            WHERE tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
                AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
                AND tbl_links.user_id = :user_id
                and tbl_linkcountries.app_id = :app_id".$whereUser.$whereLinks.$whereCountry.$whereUsers."
            GROUP BY
                tbl_linkcountries.country_code,
                tbl_links.user_id,
                tbl_links.id
            ", [':app_id' => $app_id, ':user_id' => User::getCurrentUser()->id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);
        }else{
            $apps = $connection->createCommand("
            SELECT
                Count( * ) AS installs,
                tbl_linkcountries.country_code,
                tbl_linkcountries.app_id,
                `user`.display_name,
                `user`.email,
                `user`.id as user_id
            FROM
                tbl_devices
                INNER JOIN tbl_links ON tbl_devices.link_id = tbl_links.id
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
                INNER JOIN `user` ON `user`.id = tbl_links.user_id 
            WHERE tbl_devices.date_reg >= CAST(:date_start AS DATETIME)
                AND tbl_devices.date_reg < CAST(:date_end AS DATETIME)
                and tbl_linkcountries.app_id = :app_id".$whereUser.$whereLinks.$whereCountry.$whereUsers."
            GROUP BY
                tbl_linkcountries.country_code,
                tbl_links.user_id,
                tbl_links.id
            ", [':app_id' => $app_id, ':date_start' => $dateStartFormat, ':date_end' => $dateEndFormat]);
        }
        $apps = $apps->queryAll();

        $statsData['total_installs'] = 0;
        $statsData['total_profit'] = 0;

        foreach ($apps as $app) {

            $price = Prices::find()
                ->where(['app_id' => $app['app_id']])
                ->andWhere(['user_id' => $app['user_id']])
                ->andWhere(['country_code' => $app['country_code']])
                ->one();

            if(!$price){
                $price = Prices::find()
                    ->where(['app_id' => $app['app_id']])
                    ->andWhere(['user_id' => $app['user_id']])
                    ->andWhere(['country_code' => "all"])
                    ->one();
            }

            if (!$price) {
                $price = Prices::find()
                    ->where(['app_id' => $app['app_id']])
                    ->andWhere(['user_id' => $app['user_id']])
                    ->andWhere(['country_code' => -1])
                    ->one();
            }

            if(!$price) {
                $price = ['price' => 0];
            }

            if(!isset($partnersData[$app['user_id']])) {
                $partnersData[$app['user_id']] = [
                    "id" => $app['user_id'],
                    "display_name" => $app['display_name'],
                    "email" => $app['email'],
                    "installs" => $app['installs'],
                    "profit" => $app['installs']*$price['price'],
                ];
            }else{
                $partnersData[$app['user_id']]['installs'] += $app['installs'];
                $partnersData[$app['user_id']]['profit'] += $app['installs']*$price['price'];
            }
            $statsData['total_installs'] += $app['installs'];
            $statsData['total_profit'] += $app['installs']*$price['price'];
        }
        //end устанавливаем цену

        $linkCount = 0;
        foreach ($linkCountries as $link) {
            $linkCount++;
        }

        $stats['visits'] = 0;
        $stats['devices'] = 0;
        $stats['bots_devices'] = 0;
        $stats['price'] = $statsData['total_profit'];
	foreach($devices as $device){
            $countryPrice = $appPrices[$device['country_code']] ?? $appPrices['all'] ?? 0;

            $stats['price'] += ($countryPrice*$device['count']);
            $stats['devices'] += $device['count'];
        }

        foreach ($namingDevices as $namingDevice) {
            $stats['devices'] += $namingDevice['count'];
        }

        if ($linkCount > 0) {
            if(strlen($visits) <= 0){
                $visits = 0;
            }
            $stats['visits'] = $visits;
            $stats['visits_redirect'] = $visits_redirect;
            $stats['bots_devices'] = 0;//intval($bots);
            //$stats['price'] = "$".($devices-intval($bots))*$appInfo['price'];
            $stats['price'] = "$".$stats['price'];//.($devices*$appInfo['price']);//($devices-intval($bots))*$appInfo['price'];
        }

        //поиск всех меток данного приложений
        $allLabels = [];

        if (!User::hasPermission('view_all_statistics')) { //WHERE tbl_links.archived = 0
            $linksLabel = $connection->createCommand("
            SELECT
                tbl_links.label 
            FROM
                tbl_links
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id 
            WHERE
                tbl_links.user_id = :user_id 
                AND tbl_linkcountries.app_id = :app_id
            GROUP BY
                tbl_links.label
            ", [':user_id' => User::getCurrentUser()->id, ':app_id' => $app_id]);
        }else{ //WHERE tbl_links.archived = 0
            $linksLabel = $connection->createCommand("
            SELECT
                tbl_links.label 
            FROM
                tbl_links
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id 
            WHERE
                tbl_linkcountries.app_id = :app_id
            GROUP BY
                tbl_links.label
            ", [':app_id' => $app_id]);
        }
        $linksLabel = $linksLabel->queryAll();
        for($i=0; $i<count($linksLabel); $i++){
            if(strlen($linksLabel[$i]['label']) > 0) {
                $newLabel = [
                    "name" => $linksLabel[$i]['label']
                ];
                array_push($allLabels, $newLabel);
            }
        }
        //end поиск всех меток данного приложений

        $listUsers = [];
        $allUsers = $connection->createCommand("
            SELECT
                `user`.id,
                `user`.display_name 
            FROM
                tbl_links
                INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id
                INNER JOIN `user` ON `user`.id = tbl_links.user_id 
            WHERE
                tbl_linkcountries.app_id = :app_id 
            GROUP BY
                `user`.id
            ", [':app_id' => $app_id]);
        $allUsers = $allUsers->queryAll();

        foreach ($allUsers as $user) {
            $listUsers[$user['id']] = $user['display_name'];
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'date_from' => $from,
            'date_to' => $to,
            'allLinkCountries' => $allLinkCountries,
            'selectCountry' => $selectCountry,
            'appPrices' => $appPrices,
            'allLabels' => $allLabels,
            'selectLabels' => $selectLabels,
            'selectUsers' => $selectUsers,
            'listUsers' => $listUsers
        ]);
    }

//    public function getAllDevices()
//    {
//        $allDevices;
//        foreach (Devices::find()->where([])->all() as $key => $value) {
//            $allDevices[$value['id']] = $value['name'];
//        }
//        return $allDevices;
//    }

    /**
     * Displays a single Visits model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        //$otherData['allDevices'] = $this->getAllDevices();
        $otherData = "";
        return $this->render('view', [
            'model' => $this->findModel($id),
            'otherData' => $otherData,
        ]);
    }

    /**
     * Creates a new Visits model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Visits();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Visits model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//        ]);
    }

    /**
     * Deletes an existing Visits model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        //$this->findModel($id)->delete();

        //return $this->redirect(['index']);
    }

    /**
     * Finds the Visits model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Visits the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Visits::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
