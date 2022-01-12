<?php

namespace app\controllers;

use Yii;
use app\models\Links;
use app\models\Visits;
use app\models\Linkcountries;
use app\basic\debugHelper;
use app\models\Devices;
use yii\log;
use app\controllers\LogsController;

class SystemController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return [
            'modules' => [
                'debug' => [
                    'class' => \yii\debug\Module::class,
                    'panels' => [
                        'queue' => \yii\queue\debug\Panel::class,
                    ],
                ],
            ],
        ];
        return $this->render('index');
    }

    public function actionTestaf(){
        $logger = new LogsController();
        $logger->data['TESTAF'] = true;
        $logger->infoSend('PushAPI');
        debugHelper::print("FF");
    }

	public function actionDatadog(){
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.datadoghq.com/api/v2/logs/events');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


		$headers = array();
		$headers[0] = 'Content-Type: application/json';
		$headers[1] = 'Dd-Api-Key: ddad71558be0688b0160a76bdfafac2b';
		$headers[2] = 'Dd-Application-Key: a9ca8d7a3d7e91aabafbbba7dd5bb8d9f199338c';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);
		
		$result = json_decode($result);
		
		debugHelper::print($result);
	}
}
