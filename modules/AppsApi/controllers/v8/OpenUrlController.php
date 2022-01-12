<?php

namespace app\modules\AppsApi\controllers\v8;

use app\basic\ApiHelper;
use app\controllers\LogsController;
use app\models\Apps;
use app\models\Visits;

class OpenUrlController extends \yii\web\Controller
{
    public $defaultAction = 'get-url';

    public function actionGetUrl()
    {
        if (!isset($_GET['visitid']) || !isset($_GET['hash'])) exit;

        $ip = ApiHelper::getIP();
        $visit = Visits::findOne($_GET['visitid']);
        if (!isset($visit)) exit;

        $redirect_info = ApiHelper::isJSON($visit->redirect_data) ? json_decode($visit->redirect_data, true) : [];
        $logger = new LogsController();
        $logger->data['message']['visit->url'] = $visit->url ?? "none";
        $logger->data['message']['info'] = $redirect_info;
        $logger->infoSend('OpenUrlTest');
        if (!isset($redirect_info['hash']) || empty($redirect_info) || $redirect_info['hash'] != $_GET['hash']) {
            header('Location: //block.com');
            exit();
        }

        if ($visit->filterlog->ip != $ip) {
            header('Location: //block.com');
            $this->saveRedirectInfo($visit, $visit->filterlog->ip, $ip, 'block');
        } else {
            header('Location: ' . $visit->url);
            $this->saveRedirectInfo($visit, $ip, $ip, $visit->url);
        }
        exit();
    }

    public function saveRedirectInfo($visit, $ipOriginal, $ipFactual, $url)
    {
        $redirect_info = json_decode($visit->redirect_data, true);

        unset($redirect_info['hash']);
        $visit->is_redirect = 1;
        $redirect_info['ip_original'] = $ipOriginal;
        $redirect_info['ip_factual'] = $ipFactual;
        $redirect_info['url'] = $url;

        $visit->redirect_data = json_encode($redirect_info);

        $appInfo = Apps::find()->where(['id' => $visit->devices->app_id])->one();

        $redirect_info['is_open'] = true;


        $visit->save();
    }
}