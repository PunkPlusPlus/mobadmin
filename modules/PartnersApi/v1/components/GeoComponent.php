<?php

namespace app\modules\PartnersApi\v1\components;

use app\models\Apps;
use app\models\Linkcountries;
use app\models\Zones;
use app\modules\PartnersApi\v1\interfaces\validator;
use Yii;
use app\controllers\LogsController;
use app\models\Links;

class GeoComponent
{
    use validator;

    public static function validateData()
    {
        $error = ResponseComponent::getJson('error', 'Invalid data');
        $request = Yii::$app->request->post();
        if (!self::checkData('geo')) return $error;
        $data = $request;
        $data['geo'] = $request['geo'];
        if (is_array($data['geo'])) {
            return $data;
        }
        return $error;
    }
    /**
     * @param $app
     * @param $data
     * @return array|bool
     */
    public static function createGeo($app, $data)
    {
        $countries = Linkcountries::selectByApp($app->id);
        $listCountry = Zones::getZones();
        $invalid_geo = array();
        foreach ($data['geo'] as $geo) {
            $geo = strtolower($geo);
            $linkcountry = new Linkcountries();
            $linkcountry->app_id = $app->id;
            $linkcountry->country_code = $geo;
            $linkcountry->archived = 0;
            if (!in_array($geo, $listCountry) || in_array($geo, $countries) || !$linkcountry->save()) {
                array_push($invalid_geo, $geo);
            }
        }
        if (count($invalid_geo) > 0) {
            return $invalid_geo;
        }
        return true;
    }

    public static function getResponse($result)
    {
        if (is_array($result)) {
            return ResponseComponent::getJson('error', ['invalid countries' => $result]);
        }
        return ResponseComponent::getJson('ok', '');
    }

    public static function remove($app, $data)
    {
        $message = array();
        $deletedCountries = array();
        foreach ($data['geo'] as $geo) {
            $geo = strtolower($geo);
            $linkcountry = Linkcountries::find()
                ->where(['country_code' => $geo])
                ->andWhere(['app_id' => $app->id])
                ->andWhere(['archived' => 0])->one();
            $message['geo'] = $geo;
            $message['model'] = $linkcountry;
            if (!$linkcountry) continue;
            $linkcountry->archived = 1;
            if ($linkcountry->save()) {
                Links::updateAll(['archived' => 1], ['=', 'linkcountry_id', $linkcountry->id]);
                array_push($deletedCountries, $geo);
            } else $message['error'] = "no save";
            $logger = new LogsController();
            $logger->data['message'] = $message;
            $logger->infoSend("RemoveGeo");
        }
        return $deletedCountries;
    }

}
