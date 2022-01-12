<?php


namespace app\controllers;

use app\models\notification\TelegramBot;


class TelegramController extends \yii\web\Controller
{
    public function beforeAction($action)
    {            
        if ($action->id == 'index') {
            $this->enableCsrfValidation = false;
        }
    
        return parent::beforeAction($action);
    }
    
    public function actionIndex()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        $data = isset($data['callback_query']) ? $data['callback_query'] : $data['message'];

        if(isset($data['text'])) {
            $message = mb_strtolower($data['text'], 'utf-8');
            $chat_id = $data['chat']['id'];

            $tg_bot = new TelegramBot($chat_id);
            switch($message) {
                case '/getchatid':
                case '/getchatid@profitnetworkbot':
                    $tg_bot->sendMessage('Ваш чат ID:');
                    $tg_bot->sendMessage($chat_id);
                    break;
                default:
                    $tg_bot->sendMessage('Я знаю только одну команду - /getchatid');
                    break;
            }
        }
        
        return true;
    }
}