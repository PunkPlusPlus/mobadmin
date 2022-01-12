<?php

namespace app\modules\AppsApi\traits;

use app\controllers\LogsController;

trait LoggingTrait
{
    private static function sendLog($array, string $method)
    {
        $logger = new LogsController();
        $logger->data = $array;
        $logger->infoSend($method);
    }
}