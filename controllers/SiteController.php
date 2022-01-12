<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

use app\models\Prelands;
use app\models\Devices;
use app\models\Visits;
use app\models\Apps;
use app\models\Users;
use app\basic\debugHelper;

class SiteController extends Controller
{
    public $freeAccessActions = ['change-language'];
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

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    

    public function actionChangeLanguage($lang){
        $cookies = Yii::$app->response->cookies;

        $cookies->add(new \yii\web\Cookie([
            'name' => 'language',
            'value' => $lang,
            'expire' => time() + 86400 * 365,
        ]));

        return $this->redirect('/');
    }
    
    public function actionIndex()
    {
        return $this->redirect(['/apps/index?sort=-id']);

        return $this->render('index', [
			'' => ''
        ]);
    }

}
