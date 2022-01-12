<?php


namespace app\controllers;

use yii\web\Controller;

class BaseAdminController extends Controller
{

    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }


}
