<?php

namespace app\controllers;

use Yii;
use app\models\Notifications;
use app\models\Apps;
use app\basic\debugHelper;
use webvimark\modules\UserManagement\models\User;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class NotificationsController extends \yii\web\Controller{
    public static $source = [
        1 => "Adminpanel",
        2 => "Telegram",
        3 => "Slack",
        4 => "Email",
        5 => "SMS"
    ];

    public function actionAdd()
    {
        $user_id = Yii::$app->request->post('Notifications')['user_id'] ?? null;
        if(User::hasPermission('change_notifications') || $user_id == User::getCurrentUser()->id) {
            $newNotify = new Notifications();
            if($newNotify->load(Yii::$app->request->post()) && $newNotify->validate()) {
                $newNotify->save();
                Yii::$app->session->setFlash('success', 'Уведомление добавлено!');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        throw new MethodNotAllowedHttpException('Недостаточно прав');
    }

    public function actionDelete($id)
    {
        $notify = Notifications::find()->where(['id' => $id])->one();
        if(!$notify) {
            throw new NotFoundHttpException('Уведомление не найдено');
        }
        
        if(User::hasPermission('change_notifications') || $notify->user_id == User::getCurrentUser()->id) {
            $notify->delete();
            return $this->redirect(Yii::$app->request->referrer);
        }

        throw new MethodNotAllowedHttpException('Недостаточно прав');
    }

    public function actionMakeMain($id, $user_id)
    {
        $oldMainNotify = Notifications::find()->where([
            'is_main' => 1,
            'user_id' => $user_id
        ])->one();
        $newMainNotify = Notifications::findOne($id);

        if(empty($newMainNotify)) {
            throw new NotFoundHttpException('Уведомление не найдено');
        }
        if(!empty($oldMainNotify)) {
            $oldMainNotify->is_main = 0;
            $oldMainNotify->save();
        }
        
        $newMainNotify->is_main = 1;
        $newMainNotify->save();

        Yii::$app->session->setFlash('success', 'Основной канал изменен');
        return $this->redirect(Yii::$app->request->referrer);
    }
}