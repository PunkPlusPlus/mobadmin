<?php

namespace app\modules\PartnersApi\v1\controllers;

use app\models\Tokens;
use app\modules\PartnersApi\v1\components\LinkComponent;
use webvimark\modules\UserManagement\models\User;
use yii\rest\Controller;



class LinksController extends BaseController
{
    public function actionOrganic()
    {
        $data = LinkComponent::validateData();
        if (is_string($data)) return $data;
        $token = Tokens::findOne(['token' => $data['token']]);
        $user = User::findOne($token->user_id);
        return LinkComponent::createLink($data, $user, 1);
    }

    public function actionNaming()
    {
        $data = LinkComponent::validateData();

        if (is_string($data)) return $data;
//      var_dump($data); die;
        $token = Tokens::findOne(['token' => $data['token']]);
        $user = User::findOne($token->user_id);

        return LinkComponent::createLink($data, $user);
    }

    public function actionGetStats()
    {
        $stats = LinkComponent::getStats();
        return $stats;
    }
}

