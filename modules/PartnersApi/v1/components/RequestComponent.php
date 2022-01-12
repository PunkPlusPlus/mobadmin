<?php

namespace app\modules\PartnersApi\v1\components;
use app\controllers\LogsController;
use app\models\Apps;
use app\models\Tokens;
use app\modules\PartnersApi\v1\components\ResponseComponent as Response;
use webvimark\modules\UserManagement\models\User;
use app\modules\PartnersApi\v1\interfaces\validator;

class RequestComponent
{
    use validator;

    public static $response = false;

    public static function checkToken($request)
    {
        if (!self::checkData('token')) {
            self::$response = Response::getJson('error', 'Missing token');
            return;
        }
        $token = Tokens::findOne(['token' => $request['token']]);
        if (!$token) {
            self::$response = Response::getJson('error', 'Invalid token');
	    
        }
        return $token;
    }

    public static function checkUser($token)
    {
        $user = User::findOne($token->user_id);
$logger = new LogsController();
        $logger->data['message']['token'] = json_encode($token);
        $logger->data['message']['user'] = json_encode($user);
	$logger->infoSend('test');	
        if (!$user) {
            self::$response = Response::getJson('error', 'Authentication failed');
        }
        return $user;
    }

    public static function checkApp($user, $request)
    {
        if (!self::checkData('package')) {
            self::$response = Response::getJson('error', 'Invalid data');
            return;
        }
        $app = Apps::findOne(['package' => $request['package']]);
        if (!$app) {
            self::$response = Response::getJson('error', 'No app was found');
            return;
        }
        $appAccess = Apps::getAccessApp($user->id, $app->id);
        if (!$appAccess) {
            self::$response = Response::getJson('error', 'No access');
            return;
        }
        return $app;
    }

}
