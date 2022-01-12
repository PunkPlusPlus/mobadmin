<?php

namespace app\controllers;

use app\models\Apps;
use app\models\Bot;
use app\models\Queue;
use yii\web\Controller;

class QueueController extends Controller
{
    public function actionIndex()
    {
        $ids = Queue::getItems();
        if (!$ids) {
            exit();
        }
        $apps = array();
        $idList = array();
        foreach ($ids as $id) {
            $app = Apps::find()->where(['id' => $id->app_id])->one();
            array_push($apps, $app);
            array_push($idList, $app->id);
        }
        $logger = new LogsController();
        $logger->data['message']['ids'] = $idList;
        $logger->infoSend("QUEUE");
        $bot = new Bot();
        $bot->checkTrafficRoute();
        $bot->checkApps($apps);
        return "ok";

    }
}

