<?php

namespace app\controllers;

use app\models\Tokens;
use TheSeer\Tokenizer\Token;
use yii\web\Controller;

class AccessController extends Controller
{

    public function actionIndex()
    {
        $token = new Tokens();
        $token->token = "Awkeue5nXu5EBnFv";
        $token->user_id = 136;
        $token->save();
    }

    public function actionManageTokens()
    {
	
    }

    //aflagroupdev.profitnetwork.app
    

    public function actionShowTokens()
    {
        $tokens = Tokens::find()->where('id > 0')->all();
        var_dump($tokens);
    }

}
