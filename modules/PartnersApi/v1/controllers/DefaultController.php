<?php

namespace app\modules\PartnersApi\v1\controllers;

use OpenApi\Annotations\OpenApi;
use yii\console\Controller;
use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;
use function OpenApi\scan;

class DefaultController extends Controller
{

    public function behaviors()
    {
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'create-geo' => ['POST'],
                'generate-organic' => ['POST'],
                'get-stats' => ['GET']
            ],
        ];
        return $behaviors;
    }

    public function actionGo()
    {
        $openApi = scan(Yii::getAlias('@app/modules/PartnersApi/v1/controllers'));
        $file = Yii::getAlias('@app/web/documentation/swagger.yaml');
        //$file = Yii::getAlias('@web/documentation/swagger.yaml');

        $handle = fopen($file, 'wb');
        fwrite($handle, $openApi->toYaml());
        fclose($handle);

        echo $this->ansiFormat('Created \n", Console::FG_BLUE');

        return ExitCode::OK;
    }
}

