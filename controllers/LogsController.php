<?php

namespace app\controllers;

use Yii;
use app\models\Logs;
use app\models\LogsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use webvimark\modules\UserManagement\models\User;
use app\basic\debugHelper;

/**
 * LogsController implements the CRUD actions for Logs model.
 */

class LogsController
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
			
			'ghost-access'=> [
				'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
			],
        ];
    }
	public $freeAccess = true;
    public $data = [];
    public $errors = [];
    public $startTime = 0;
    public $logId = -1;

    public function __construct()
    {
        $this->startTime = microtime(true);//отслеживанием скорость выполнения запроса
        $this->logId = date_timestamp_get(date_create());
    }


    public function infoSend($method){
        $this->getIP();
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger();
        $this->endLog();
        $data = $this->collectData();
        return $logger->info($method, $data);
    }
    public function errorSend($method){
        $this->getIP();
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger();
        $this->endLog();
        $data = $this->collectData();
        return $logger->error($method, $data);
    }
    public function warningSend($method){
        $this->getIP();
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger();
        $this->endLog();
        $data = $this->collectData();
        return $logger->warning($method, $data);
    }

    public function endLog(){
        $totalTime = microtime(true)-$this->startTime;
        $this->data['speed_time'] = $totalTime;

        $serverName = $_SERVER['SERVER_NAME'];
        $requestURI = $_SERVER['REQUEST_URI'];
        if(isset($_POST) && count($_POST) > 0) $this->data['post'] = $_POST;

        $this->data['server_name'] = $serverName;
        $this->data['requestURI'] = urldecode($requestURI);
        $this->data['logId'] = $this->logId;
    }

    public function collectData(){
        $data = array_merge($this->data, $this->errors);
        return $data;
    }

    public function getIp(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->data['user']['ip'] = $ip;
    }

    public function logCreateCountry($country_code, $app_package) {
        $time = date("Y-m-d H:i:s");
        $user = User::getCurrentUser();
        $this->data['_message'] = 'CreateCountry';
        $this->data['info'] = [
            'country_code' => $country_code,
            'app' => $app_package,
            'time' => $time
        ];
        $this->data['user']['id'] = $user->id;
        $this->data['user']['name'] = $user->display_name;
        $this->infoSend('ActionChange');
    }

    public function logCreateLink($link) {
        $this->data['_message'] = 'CreateLink';
        $this->data['app'] = [
            'package' => $link->linkcountry->app->package
        ];
        $this->data['info'] = [
            'link' => [
                'linkcountry_id' => $link->linkcountry_id,
                'user_id' => $link->user_id,
                'is_main' => $link->is_main,
                'key' => $link->key,
                'value' => $link->value,
                'url' => $link->url,
                'label' => $link->label
            ]
        ];

        $this->getUserInfo();
        $this->infoSend('ActionChange');
    }

    public function logUpdateLink($old_link, $link) {
        $fields = ['linkcountry_id', 'user_id', 'is_main', 'key', 'value', 'url', 'label'];

        $this->addInfoChanges($link, $old_link, $fields);
        if($this->data['info']['changes'] === []) return false;

        $this->data['_message'] = 'ChangeLink';
        $this->data['app'] = [
            'package' => $link->linkcountry->app->package
        ];

        $this->getUserInfo();
        $this->infoSend('ActionChange');
    }

    public function logCreateParam($param) {
        $this->data['_message'] = 'CreateParam';
        $this->data['app'] = [
            'package' => $param->app->package
        ];
        $this->data['info'] = [
            'param' => [
                'app_id' => $param->app_id,
                'user_id' => $param->user_id,
                'linkcountry_id' => $param->linkcountry_id,
                'key' => $param->key,
                'value' => $param->value,
                'access_level' => $param->access_level,
            ]
        ];

        $this->getUserInfo();
        $this->infoSend('ActionChange');
    }

    public function logUpdateParam($old_param, $param) {
        $fields = ['app_id', 'user_id', 'linkcountry_id', 'key', 'value', 'access_level'];

        $this->addInfoChanges($param, $old_param, $fields);
        if($this->data['info']['changes'] === []) return false;

        $this->data['_message'] = 'ChangeParam';
        $this->data['app'] = [
            'package' => $param->app->package
        ];

        $this->getUserInfo();
        $this->infoSend('ActionChange');
    }

    public function getUserInfo() {
        $user = User::getCurrentUser();
        $this->data['user']['id'] = $user->id;
        $this->data['user']['name'] = $user->display_name;
    }

    private function addInfoChanges($param, $old_param, $fields) {
        $this->data['info']['changes'] = [];
        foreach($fields as $field) {
            if($param->$field != $old_param->$field) {
                $this->data['info']['changes']['_old_'.$field] = $old_param->$field;
                $this->data['info']['changes'][$field] = $param->$field;
            }
        }
        ksort($this->data['info']['changes']);
    }

    /**
     * Lists all Logs models.
     * @return mixed
     */
    public function actionIndex()
    {
		if(!User::hasPermission('logs_view')){
			print "Access denied";
			exit();
		}

        $searchModel = new LogsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Logs model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		if(!User::hasPermission('logs_view')){
			print "Access denied";
			exit();
		}
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

	public function actionSend($title = 'Log', $text, $type = 0){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip=$_SERVER['REMOTE_ADDR'];
		}


		$log = new Logs();
		$log->user_ip = $ip;
		$log->title = $title;
		$log->text = $text;
		/*
		types:
			0 - mobile
		*/
		$log->type = $type;
		$log->save();
	}


    /**
     * Deletes an existing Logs model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
		if(!User::hasPermission('logs_view')){
			print "Access denied";
			exit();
		}
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Logs model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Logs the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Logs::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
