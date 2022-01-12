<?php

namespace app\modules\PartnersApi\v1\components;

class ResponseComponent
{
    public static function getJson($code, $message)
    {
        return json_encode(
            [
                'code' => $code,
                'message' => $message
            ]
        );
    }
}
