<?php

namespace app\modules\PartnersApi\v1\interfaces;



trait validator
{
    static function getJson($status, $message)
    {
        return json_encode(
            [
                'status' => $status,
                'message' => $message
            ]
        );
    }

    static function checkData()
    {
        $request = \Yii::$app->request->post();
        foreach (func_get_args() as $arg) {
            if (!isset($request[$arg])) {
                return false;
            }
        }
        return true;
    }
}




