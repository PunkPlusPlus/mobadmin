<?php

namespace app\controllers;

use app\models\Prices;
use Yii;
use app\models\Linkcountries;
use app\models\LinkcountriesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use app\models\Users;
use app\models\Apps;
use app\models\Links;
use app\models\AppsAccess;
use app\basic\debugHelper;
use webvimark\modules\UserManagement\models\User;

/**
 * LinkcountriesController implements the CRUD actions for Linkcountries model.
 */
class LinkcountriesController extends Controller
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
     * Lists all Linkcountries models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect('/apps/index');

        $searchModel = new LinkcountriesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $otherData['allUsers'] = $this->getAllUsers();
        $otherData['allApps'] = $this->getAllApps();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'otherData' => $otherData
        ]);
    }

    /**
     * Displays a single Linkcountries model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        exit();
        $otherData['allUsers'] = $this->getAllUsers();
        $otherData['allApps'] = $this->getAllApps();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'otherData' => $otherData
        ]);
    }

    public static function createOne($country_code, $app_id, $extra = false)
    {
        $newCountry = new Linkcountries();
        $newCountry->country_code = $country_code;
        $newCountry->app_id = $app_id;
        if ($extra)
            $newCountry->extra = $extra;
        if($newCountry->save()){
            $newLink = new Links();
            $newLink->linkcountry_id = $newCountry->id;
            $newLink->user_id = 48;
            $newLink->key = "main";
            $newLink->value = "yes";
            $newLink->url = "block";
            $newLink->is_main = 1;
            $newLink->save();
        }
    }

    /**
     * Creates a new Linkcountries model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($appid)
    {
        if (!User::hasPermission('view_all_apps')) {
            $accessApp = Linkcountries::find()
                ->where(['app_id' => $appid])
                ->andWhere(['user_id' => User::getCurrentUser()->id])
                ->andWhere(['archived' => 0]);

            if (!$accessApp->one()) {
                header('HTTP/1.0 403 Forbidden');
                echo '403 Forbidden';
                exit();
            }
        }

        $app = Apps::find()
            ->where(['id' => $appid])
            ->one();

        $errorText = '';
        $model = new Linkcountries();

        if ($model->load(Yii::$app->request->post())) {
            $model->app_id = $appid;
            $errorText = $this->checkAlreadyCountry($model, true);
            if (strlen($errorText) <= 0 && $model->save()) {
                $logger = new LogsController();
                $logger->logCreateCountry($model->country_code, $app->package);

                return $this->redirect(['apps/view', 'id' => $model->app_id]);
            }
        }

        $appInfo = [
            'id' => $app->id,
            'name' => $app->name,
            'package' => $app->package,
            //'extra' => json_decode($app->extra)
        ];


        return $this->render('create', [
            'model' => $model,
            'appInfo' => $appInfo,
            'errorText' => $errorText
        ]);
    }

    /**
     * Updates an existing Linkcountries model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $linkCountry = Linkcountries::find()
            ->where(['id' => $model->id])
            ->andWhere(['archived' => 0])
            ->one();
//        if (!User::hasPermission('view_all_apps')) {
//            //->andWhere(['user_id' => User::getCurrentUser()->id])
//            if (!$accessApp->one()) {
//                header('HTTP/1.0 403 Forbidden');
//                echo '403 Forbidden';
//                exit();
//            }
//        }

        $errorText = '';
        $errorDeeplinks = false;
        if ($model->load(Yii::$app->request->post())) {
            $model->country_code = $linkCountry->country_code;
            $model->app_id = $linkCountry->app_id;

            if (!User::hasPermission('edit_all_apps')) {
                $model->extra = $linkCountry->extra;
            }

            if(strlen($_POST['new_urls']) > 3){
                $errorDeeplinks = json_encode($this->addUrls($model->id, $_POST['new_urls']));
            }else{
                $errorDeeplinks = false;
            }
//            if (!$errorDeeplinks && $model->save()) {
//                return $this->redirect(['apps/view', 'id' => $model->app_id]);
//            }
            $model->save();
        }

        $app = Apps::find()
            ->where(['id' => $model->app_id])
            ->one();


        $appInfo = [
            'id' => $app->id,
            'name' => $app->name,
            'package' => $app->package,
            //'extra' => json_decode($app->extra)
        ];

        if (User::hasPermission('edit_all_apps')) {
            $links = Links::find()
                ->where(['linkcountry_id' => $model->id])
                ->andWhere(['archived' => 0])
                ->all();
        }else{
            $links = Links::find()
                ->where(['linkcountry_id' => $model->id])
                ->andWhere(['user_id' => User::getCurrentUser()->id])
                ->andWhere(['archived' => 0])
                ->all();
        }

        //поиск всех меток данного приложений
        $allLabels = [];
        $linksLabel = Links::find()
            ->where(['linkcountry_id' => $model->id])
            ->andWhere(['archived' => 0])
            ->andWhere(['user_id' => User::getCurrentUser()->id])
            ->all();
        foreach ($linksLabel as $linkLabel){
            $newLabel = [
                "name" => $linkLabel->label
            ];
            array_push($allLabels, $newLabel);
        }
        //end поиск всех меток данного приложений


        return $this->render('update', [
            'model' => $model,
            'appInfo' => $appInfo,
            'errorText' => $errorText,
            'links' => $links,
            'errorDeeplinks' => $errorDeeplinks,
            'allLabels' => $allLabels
        ]);
    }

    public function addUrls($linkcountry_id, $jsonUrls){
        $urls = json_decode($jsonUrls, true);
        $errorUrls = $urls;

        for($i=0; $i<count($urls); $i++){
            $user_id = $urls[$i]['user_id'];
            $key = trim($urls[$i]['key']);
            $value = trim($urls[$i]['value']);
            $url = trim($urls[$i]['url']);
            $linkid = $urls[$i]['link_id'] ?? false;
            $label = $urls[$i]['label'] ?? false;
            if(strlen($label) <= 0) $label = false;


            if (User::hasPermission('edit_all_apps')) {
                $isMain = $urls[$i]['is_main'] ? 1:0;
            }else{
                $isMain = 0;
                $user_id = User::getCurrentUser()->id;
            }

            if(!$linkid) {
                $link = Links::find()
                    ->where(['linkcountry_id' => $linkcountry_id])
                    ->andWhere(['key' => $key])
                    ->andWhere(['value' => $value])
                    ->andWhere(['archived' => 0])
                    ->one();

            }else{
                $link = Links::find()
                    ->where(['id' => $linkid])
                    ->one();

                if(!$link)
                    die('403 Forbidden');
            }


            if($link && $link->id == $linkid) {
                $sameLink = Links::find()
                    ->where(["!=", "id", $linkid])
                    ->andWhere(['linkcountry_id' => $linkcountry_id])
                    ->andWhere(['key' => $key])
                    ->andWhere(['value' => $value])
                    ->andWhere(['archived' => 0])
                    ->one();
                if($sameLink) continue;
            }


            if( !$link || $linkid ){

                foreach($errorUrls as $keyError=>$valueError){
                    if($errorUrls[$keyError]['key'] == $key && $errorUrls[$keyError]['value'] == $value){
                        unset($errorUrls[$keyError]);
                    }
                }


                if($isMain) {
                    $changeMain = Links::find()
                        ->where(['linkcountry_id' => $linkcountry_id])
                        ->andWhere(['is_main' => 1])
                        ->andWhere(['archived' => 0]);
                    $changeMain = $changeMain->one();
                    if($changeMain && $changeMain->id != $linkid) {
                        $changeMain->is_main = 0;
                        $changeMain->save();
                    }
                }

                $this->createDefaultPrice($linkcountry_id, $user_id);

                if(!$linkid) {
                    $link = new Links();
                } else {
                    $old_link = clone $link;
                }

                $link->linkcountry_id = $linkcountry_id;
                $link->user_id = $user_id;
                $link->is_main = intval($isMain);
                $link->key = $key;
                $link->value = $value;
                $link->url = $url;
                if($label) $link->label = $label; else $link->label = null;

                $logger = new LogsController();
                if(!$linkid) {
                    $logger->logCreateLink($link);
                } else {
                    $logger->logUpdateLink($old_link, $link);
                }

                $link->save();
                $this->checkEmptyMainURL($link->linkcountry_id);
            }
        }
        //exit();
        return $errorUrls;
    }

    public function checkAlreadyCountry($formModel, $isNew = false)
    {
        //debugHelper::print($formModel);
        $errorText = '';

        $link = Linkcountries::find()
            ->where(['country_code' => $formModel['country_code']])
            ->andWhere(['app_id' => $formModel['app_id']])
            ->andWhere(['archived' => 0]);

        if (!$isNew) {
            $link->andWhere(['<>', 'id', $formModel['id']]);
            $link = $link->all();
        } else {
            $link = $link->all();
        }

        if (!$link && $isNew) {
            $link = Linkcountries::find()
                ->where(['country_code' => $formModel['country_code']])
                ->andWhere(['app_id' => $formModel['app_id']])
                ->andWhere(['archived' => 0])
                ->all();
        }
        if ($link) {
            $errorText = 'Данная страна уже занята';
        }

        return $errorText;
    }


    public function actionDeletelink($id){
        $link = Links::find()
            ->where(['id' => $id]);

        if (!User::hasPermission('edit_all_apps')) {
            $link->andWhere(['user_id' => User::getCurrentUser()->id]);
        }

        if($link = $link->one()){
            $link->archived = 1;
            if($link->save()) {
                $this->checkEmptyMainURL($link->linkcountry_id);
                return $this->redirect(['linkcountries/update', 'id' => $link->linkcountry_id]);
            }else{
                die("Ошибка архивации ссылки");
            }
        }else{
            die("Ошибка удаления ссылки");
        }
    }

    public function checkEmptyMainURL($linkcountryId){
        $mainLink = Links::find()
            ->where(['linkcountry_id' => $linkcountryId])
            ->andWhere(['archived' => 0])
            ->andWhere(['is_main' => 1]);

        if(!$mainLink = $mainLink->one()){
            $changeLink = Links::find()
                ->where(['linkcountry_id' => $linkcountryId])
                ->andWhere(['archived' => 0])
                ->andWhere(['is_main' => 0]);

            if($changeLink = $changeLink->one()) {
                $changeLink->is_main = 1;
                $changeLink->save();
            }
        }
    }

    /**
     * Deletes an existing Linkcountries model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (!User::hasPermission('edit_all_apps')) {
            header('HTTP/1.0 403 Forbidden');
            die('403 Forbidden');
        }
        $model = $this->findModel($id);
        if ($model->country_code == "all") {
            header('HTTP/1.0 403 Forbidden');
            echo 'Вы не можете удалить эту связь';
            exit();
        }
        if (!User::hasPermission('view_all_apps')) {

            $accessApp = Linkcountries::find()
                ->where(['id' => $id])
                ->andWhere(['archived' => 0]);

            if (!$accessApp = $accessApp->one()) {
                header('HTTP/1.0 403 Forbidden');
                echo '403 Forbidden';
                exit();
            }



        }

        $model->archived = 1;
        if($model->save()){
            Links::updateAll(['archived' => 1], ['=', 'linkcountry_id', $model->id]);
        }
        if (isset($_GET['app_id']))
            return $this->redirect(['apps/view', 'id' => $_GET['app_id']]);

        return $this->redirect(['/apps/index']);
    }

    /**
     * Finds the Linkcountries model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Linkcountries the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Linkcountries::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function createDefaultPrice($linkcountry_id, $user_id) {
        $is_partner = \app\models\PartnerBalance::find()->where(['partner_id' => $user_id])->count();
        if($is_partner) {
            $linkcountry = Linkcountries::findOne($linkcountry_id);
            $app_id = $linkcountry->app_id;
            $price = Prices::find()->where(['user_id' => $user_id, 'app_id' => $app_id])->one();
            if(!$price) {
                $price = new Prices();
                $price->user_id = $user_id;
                $price->app_id = $app_id;
                $price->country_code = 'all';
                $price->price = 0.10;
                $price->save();
            }
        }
    }
}
