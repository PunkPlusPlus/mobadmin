<?php

namespace app\modules\AppsApi\components\v8;

use app\models\Apps;
use app\modules\AppsApi\traits\LoggingTrait;
use Yii;

class RequestComponent
{
    use LoggingTrait;

    private static $request;
    private static $log = array();

    //TODO logging
    public static function validateData()
    {
        $request = Yii::$app->request;
        $data = $request->post('data') ?? false;
        //$data = json_encode($data);

        if (!$data) {
            self::$log['errors'] = 'Invalid json format (no data)';
            self::sendLog(self::$log, "ApiRequest");
            return false;
        }
        $data = self::checkGetOnly($data);
        //$data = json_encode($data);
        //self::$log['firstData'] = json_encode($data);
        //exit($data);
        if (!ApiHelper::isJSON($data) || !isset(json_decode($data)->package)) {
            self::$log['errors'] = "Invalid json format";
	        self::$log['data'] = $data;
            self::$log['isJson'] = ApiHelper::isJson($data);
            self::sendLog(self::$log, "ApiRequest");
            return false;
        }

        self::$request = json_decode($data);
        self::$log['app']['package'] = self::$request->package ?? null;

        if (!Apps::getApp(self::$request->package)) {
            self::$log['errors'] = "Package not found";
            self::sendLog(self::$log, "ApiRequest");
            return false;
        }

        return self::$request;
    }

    private static function checkGetOnly($data)
    {
        if (!isset($_GET['getonly'])) {
            $data = base64_decode($data);

        } else {
            self::$log['getonly'] = true;
            $data = $_GET['data'] ?? null;
        }

        return $data;
    }


    public static function isDoubleAuth(): bool
    {
        if (isset(self::$request->id) && self::$request->id != -1) {
            return true;
        }
        return false;
    }

}
