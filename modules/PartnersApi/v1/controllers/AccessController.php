<?php

namespace app\modules\PartnersApi\v1\controllers;

use app\models\Tokens;
use webvimark\modules\UserManagement\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

class AccessController extends \yii\web\Controller
{
    public function actionIndex()
    {
        if (!User::hasPermission('manage-tokens')) {
            throw new HttpException(403, 'Forbidden');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $tokens = Tokens::find()->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tokens' => $tokens
        ]);
    }

    public function actionCreate($id)
    {
        if (!User::hasPermission('manage-tokens')) {
            throw new HttpException(403, 'Forbidden');
        }
        $token = Tokens::find()->where(['user_id' => $id])->one();
        if ($token) throw new HttpException(403, 'Forbidden');
        $token = new Tokens();
        $token->user_id = $id;
        $token->token = Yii::$app->security->generateRandomString(16) . md5($token->user_id);
        $token->save();
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $tokens = Tokens::find()->all();
        return $this->redirect(['/apiv1/access/index',
            'dataProvider' => $dataProvider,
            'tokens' => $tokens
        ]);
    }

    public function actionEdit($id)
    {
        if (!User::hasPermission('manage-tokens')) {
            throw new HttpException(403, 'Forbidden');
        }
        $token = Tokens::find()->where(['user_id' => $id])->one();
        if (!$token) throw new HttpException(403, 'Forbidden');
        $value = Yii::$app->security->generateRandomString(16) . md5($token->user_id);
        $token->token = $value;
        $token->save();
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $tokens = Tokens::find()->all();
        return $this->redirect(['/apiv1/access/index',
            'dataProvider' => $dataProvider,
            'tokens' => $tokens
        ]);
    }

    public function actionDelete($id)
    {
        if (!User::hasPermission('manage-tokens')) {
            throw new HttpException(403, 'Forbidden');
        }
        $token = Tokens::find()->where(['user_id' => $id])->one();
        if (!$token) throw new HttpException(403, 'Forbidden');
        $token->delete();
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $tokens = Tokens::find()->all();
        return $this->redirect(['/apiv1/access/index',
            'dataProvider' => $dataProvider,
            'tokens' => $tokens
        ]);
    }
}
