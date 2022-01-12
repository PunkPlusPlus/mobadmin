<?php

namespace app\modules\AppsApi\controllers\v8;

use app\basic\ApiHelper;
use app\controllers\LogsController;
use app\models\Visits;
use app\modules\AppsApi\components\v8\RequestComponent;
use app\modules\AppsApi\components\v8\UrlContext;
use app\modules\AppsApi\components\v8\UrlFactory;
use app\modules\AppsApi\components\v8\ResponseConfig;
use yii\rest\Controller;

/**
 * Default controller for the `AppsApi` module
 */
class AuthController extends Controller
{
    public $enableCsrfValidation = false;
    public $defaultAction = 'auth';

    /**
     * Default action for the AuthController
     * Returns json for apps     *
     * @return string
     */
    public function actionAuth()
    {
        $result = RequestComponent::validateData();

        if (!$result) return json_encode(ResponseConfig::getInstance()->logMessage);

        $handler = new UrlContext(UrlFactory::getUrlStrategy($result));
        $response = $handler->execute();

        return $response;
    }
}
