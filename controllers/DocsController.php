<?php

namespace app\controllers;
use Yii;
use webvimark\modules\UserManagement\models\User;
use app\basic\debugHelper;

class DocsController extends \yii\web\Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'ghost-access'=> [
                'class' => 'webvimark\modules\UserManagement\components\GhostAccessControl',
            ],
        ];
    }

    public function actionIndex($page)
    {
        switch ($page){
            case "conversions":
                $title = Yii::t('app', 'get_conversions');
                $template = "conversions.php";
                break;
            case "fb_integration":
                $title = Yii::t('app', 'fb_integration');
                $template = "fb_integration.php";
                break;
            case "balance":
                $title = Yii::t('app', 'partner_balance');
                $template = "balance.php";
                break;
        }

        return $this->render('index', [
            'title' => $title,
            'template' => $template
        ]);
    }

}
