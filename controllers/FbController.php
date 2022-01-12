<?php


namespace app\controllers;
use app\models\Apps;
use app\components\FacebookComponent;
use app\models\Devices;
use app\models\PostbacksIncome;
use yii\web\Controller;

class FbController extends BaseAdminController
{
    public function actionIndex($id = null)
    {
        if ($id == null) {
            $app = false;
        }else {
            $app = Apps::findOne($id);
        }
        $data['app'] = $app;
        return $this->render('index', ['data' => $data]);
    }

    public function actionRat()
    {
        //Devices::find()->where("idfa != 0")->limit($limit)->all();
        $postbacks = PostbacksIncome::find()->limit(10)->all();
        $arr = Devices::find()->limit(10)->all();
        var_dump($arr);
    }

    public function actionExecute($id = null)
    {
        if ($id == null) {
            $app = false;
        }else {
            $app = Apps::findOne($id);
        }

        $from = $_REQUEST['from'] ?? -1;
        $to = $_REQUEST['to'] ?? -1;
        $app_id = $_REQUEST['app_id'] ?? $app->fb_app_id;
        $app_secret = $_REQUEST['app_secret'] ?? $app->app_secret;
        $limit = $_REQUEST['limit'] ?? 0;

        if ($from == -1) {
            $from = date("d/m/Y", strtotime('-7 days'));
        }
        if ($to == -1) {
            $to = date("d/m/Y");
        }




        if ($app_id == null || $app_secret == null) {
            die("Не найдено app_id или app_secret");

        }
        $postbacks = FacebookComponent::getPostbacks($from, $to, $limit);
        $idfa_array = FacebookComponent::getIdfa(count($postbacks));
        $urls = FacebookComponent::constructUrl($idfa_array, $postbacks, $app_id, $app_secret);
        $response = FacebookComponent::sendMulti($urls);
        $data['app'] = $app;
        $data['from'] = $from;
        $data['to'] = $to;
        $data['response'] = $response;
        $data['app_id'] = $app_id;
        $data['secret'] = $app_secret;
        $data['limit'] = $limit;
        //return $this->redirect(['fb/index', 'data' => $data]);
        return $this->render('index', ['data' => $data]);
    }

}