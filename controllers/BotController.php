<?php

namespace app\controllers;

use app\models\Apps;
use app\models\Bot;
use app\models\notification\TelegramBot;
use yii\web\NotFoundHttpException;
use webvimark\modules\UserManagement\models\User;

class BotController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $bot = new Bot();
        $bot->checkTrafficRoute();

        $apps = Apps::find()
            ->where(['OR', ['published' => "0"], ['published' => "1"], ['published' => "2"], ['published' => "4"]])
            ->all();

        $bot->checkApps($apps);

        return "ok";
    }

    public function actionCheck($id)
    {
        if(User::hasRole('employee') || User::hasRole('Developer') || User::hasRole('manager')  || User::hasRole('support')) {
            $app = Apps::findOne($id);
            if(!$app) {
                throw new NotFoundHttpException('Приложение не найдено');
            }

            $bot = new Bot();
            $bot->checkApp($app);
        }

        $this->redirect(['apps/view', 'id' => $id]);
    }
}
