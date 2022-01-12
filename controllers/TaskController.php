<?php


namespace app\controllers;
use app\components\TaskComponent;
use app\models\Apps;
use app\models\Tasks;
use webvimark\modules\UserManagement\models\User;
use yii;

class TaskController extends yii\web\Controller
{
    public function actionIndex($id = -1)
    {
        $app = Apps::find()->where(['id' => $id])->one();
        if ($app) {
            $data['uuid'] = $app->uuid;
            $data['app_id'] = $app->fb_app_id;
            if ($data['uuid'] == null || $data['app_id'] == null) {
                header('HTTP/1.0 403 Forbidden');
                echo 'Расшар аккаунтов на это приложение временно недоступен.';
                exit();
            }
        } else {
            $data = null;
        }
        return $this->render('index',
            [
                'response' => null,
                'data' => $data,
                'app' => $app
            ]);
    }

    public function actionCreate()
    {
        $id = Yii::$app->request->post('id');
        $app = Apps::find()->where(['id' => $id])->one();
        $data = TaskComponent::checkApp($app);
        $request = Yii::$app->request->post();
        $_SESSION['params'] = $request;
        if (User::hasPermission('share_accounts_manually')) {
            $app = -1;
        }
            $jsonData = TaskComponent::formatData($request, $app);
            if ($jsonData) {
                $response = TaskComponent::createTask($jsonData);
            } else {
                $response = array(
                    'Error' => 'Ошибка отправки'
                );
                $response = json_encode($response);
            }
	    
            TaskComponent::logTask($request, $response, "CreateTask");
            $response = json_decode($response, true);
	    $logger = new LogsController();
	    $logger->data['message'] = $response;
	    $logger->infoSend('Test');
        return $this->render('index', [
            'response' => $response,
            'params' => $request,
            'data' => $data,
            'app' => $app
        ]);
    }

    public function actionGetOne($params = null)
    {
        $request = Yii::$app->request->get();
        $params = $request['params'] ?? null;
        $str = TaskComponent::formatData($request);
        $response = TaskComponent::getTaskResult($str);
        try {
            $data = json_decode($response, true);
            if ($data == null) {
                throw new \Exception();
            }
            $response = TaskComponent::mergeIds($data);
        } catch (\Exception $e) {
            $response = array('Error' => 'Ошибка сервера');
        }
        TaskComponent::logTask($request, $response, "GetTask");
        $params['response'] = $response;
        return $this->render('index', ['response' => $response, 'params' => $params]);
    }

    public function actionGetAll()
    {
        $info = TaskComponent::getAll();
        $info = TaskComponent::mergeTimestamp($info);
        //$info = TaskComponent::constructAll($info);
        return $this->render('all', ['info' => $info ?? null, 'response' => null]);
    }


}

