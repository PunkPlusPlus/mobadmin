<?php

//header("Access-Control-Allow-Origin: http://192.168.100.6:3000");
header("Access-Control-Allow-Origin: 95.142.45.162");
//print "<pre>";
//print_r($_SERVER);
//exit();
$isDebug = true;

if($_SERVER['HTTP_HOST'] == "aflagroupdev.profitnetwork.app") {
//    if (!in_array(@$_SERVER['HTTP_X_FORWARDED_FOR'], ['107.150.23.162', '107.150.23.143', '107.150.23.140'])) {
//        die('You are not allowed to access this file.');
//    }else{
//        $isDebug = true;
//    }
    $isDebug = false;
}

//if($_SERVER['REQUEST_URI'] == "/user-management/auth/login"){
//    if($_SERVER['HTTP_HOST'] != "my.joyapp.partners") {
//        die('You are not allowed to access this file.');
//    }
//}

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', $isDebug);
define('YII_ENABLE_ERROR_HANDLER', true);
defined('YII_ENV') or define('YII_ENV', $isDebug ? "dev" : "prod");

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require(__DIR__ . '/../config/aliases.php');

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
