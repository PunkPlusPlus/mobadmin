<?php

namespace app\controllers;
use app\models\AppsAccess;

class AppaccessController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }

    public function actionAdd($user_id, $app_id, $redirect = true){
        $emptyAccess = AppsAccess::find()
            ->where(['=','user_id', $user_id])
            ->andWhere(['=','app_id', $app_id]);

        if(!$emptyAccess->one()){
            $newAccess = new AppsAccess();
            $newAccess->app_id = $app_id;
            $newAccess->user_id = $user_id;
            if($newAccess->save()) {
                if ($redirect) {
                    return $this->redirect(['user-management/user/view', 'id' => $user_id]);
                }
            }else{
                print "Ошибка сохранения! Обратитесь к разработчику.";
                exit();
            }
        }else{
            print "Данное приложение уже связано с этим аккаунтом";
            exit();
        }
    }

    public function actionDel($user_id, $app_id, $redirect = true){
        $emptyAccess = AppsAccess::find()
            ->where(['=','user_id', $user_id])
            ->andWhere(['=','app_id', $app_id]);
        if($emptyAccess = $emptyAccess->one()) {
            if ($emptyAccess->delete()) {
                if ($redirect) {
                    return $this->redirect(['user-management/user/view', 'id' => $user_id]);
                }
            } else {
                print "Ошибка удаления! Обратитесь к разработчику.";
                exit();
            }
        }else{
            print "Ошибка удаления! Обратитесь к разработчику.";
            exit();
        }
    }

}
