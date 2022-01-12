<?php

namespace app\modules\PartnersApi\v1\controllers;

use app\modules\PartnersApi\v1\components\RequestComponent;
use Yii;
use yii\rest\Controller;
use app\controllers\LogsController;

class BaseController extends Controller
{

    public static function allowedDomains()
    {
    return [
        '*',                        // star allows all domains
        //'http://test1.example.com',
        //'http://test2.example.com',
    ];
    }
    
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'add-new' => ['POST'],
                    'organic' => ['POST'],
                    'naming' => ['POST'],
                    'get-stats' => ['GET']
                ],
            ],
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
            'cors' => [
                // restrict access to
                'Origin' => ['*'],
           // Allow  methods
                'Access-Control-Request-Method' => ['POST', 'PUT', 'OPTIONS', 'GET'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Headers' => ['Content-Type'],
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                //'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['*'],
            ],
            ]
        ];
    }

    public function beforeAction($action)
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request->post();
        if ($action == "get-query") {
            $request = Yii::$app->request->get();
        }
        $token = RequestComponent::checkToken($request);        
        if (RequestComponent::$response) {
            $response->data = RequestComponent::$response;
            return false;
        }
        $user = RequestComponent::checkUser($token);
        if (RequestComponent::$response) {
            $response->data = RequestComponent::$response;
            return false;
        }
        RequestComponent::checkApp($user, $request);
        if (RequestComponent::$response) {
            $response->data = RequestComponent::$response;
            return false;
        }
        return true;
    }
}

