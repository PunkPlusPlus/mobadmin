<?php

namespace app\modules\PartnersApi\v1\controllers;

use app\models\Apps;
use app\modules\PartnersApi\v1\components\GeoComponent;
use app\modules\PartnersApi\v1\components\ResponseComponent;
/**
 * @OA\Info(title="Api", version="1")
 */
class GeoController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/geo/add-new",
     *     description="Home page",
     *     tags={"Geo"},
     *
     *     @OA\Response(response="default", description="Welcome page")
     * )
     */


    public function actionRemoveGeo()
    {
        $data = GeoComponent::validateData();
        if (is_string($data)) return $data;
        $app = Apps::getApp($data['package']); //app checked in validateRequest
        $result = GeoComponent::remove($app, $data);
        $response = ResponseComponent::getJson('ok', ['deleted_geo' => $result]);
        return $response;
    }
}

