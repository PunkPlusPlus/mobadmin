<?php

namespace app\modules\PartnersApi\v1\components;

use app\models\Apps;
use app\models\Linkcountries;
use app\models\Links;
use app\models\Zones;
use app\modules\PartnersApi\v1\interfaces\validator;
use app\modules\PartnersApi\models\Stats;
use Yii;
use yii\web\Response;
use app\controllers\LogsController;

class LinkComponent
{
    use validator;

    public static function validateData()
    {
        $error = ResponseComponent::getJson('error', 'Invalid data');
	$request = Yii::$app->request->post();
        $data = $request;
	
        $zones = Zones::getZones();
        if (!self::checkData('geo', 'url')) return $error;
        $data['geo'] = $request['geo'];
	$logger = new LogsController();
        $logger->data['message'] = $data['geo'];
        $logger->infoSend('GeoValidation');
        if (!is_array($data['geo'])) return json_encode("geo is not array");
	
        foreach ($data['geo'] as $geo) {
            if (!in_array(strtolower($geo), $zones) && strtolower($geo) != 'all') {
                return json_encode("ivalid geo");
            }
            //$error = self::checkNaming($data, $geo);
            //if ($error) return $error;
        }

        return $data;
    }

    public static function checkNaming($data, $geo, $isMain = 0)
    {
	//$value = $data['value'];
	if (!self::checkData('value') || $isMain == 1) {
	    $value = 'Organic';
	} else {
	    $value = $data['value'];
	}
	$logger = new LogsController();
        $logger->data['message'] = $data;
        $logger->infoSend("NamingTest2");
        $app = Apps::getApp($data['package']);
        $linkcountry = Linkcountries::find()
            ->where(['app_id' => $app->id])
            ->andWhere(['archived' => 0])
            ->andWhere(['country_code' => $geo])
            ->one();
	if (!$linkcountry) return false;
        $links = Links::find()
            ->where(['linkcountry_id' => $linkcountry->id])
            ->andWhere(['archived' => 0])
            ->all();
        if (!$links) return false;
        foreach ($links as $link) {
            if ($link->value == $value) {
                return $link;
            }
        }
        return false;
    }

    private static function getCampName($link)
    {
        try {
            preg_match_all('{sub_\d+}', $link->url, $matches);
            $check = false;
            $result = "";
            $separator = "_";
            if ($matches[0]) {
                for ($i = 20; $i > 0; $i--) {
                    $value = 0;
                    for ($j = 0; $j < count($matches[0]); $j++) {
                        preg_match('/\d+/', $matches[0][$j], $deleted);
                        if ($deleted[0] == $i) {
                            $check = true;
                            array_splice($matches[0], $j, 1);
                            $value = 'SUB' . $i;
                            break;
                        }
                    }
                    if ($check) $result = $value . $separator . $result;
                }
                $result = preg_replace('/\_$/', "", $result);
            } else return $link->value;
        } catch (\Exception $e) {
            $logger = new LogsController();
            $logger->data['message'] = $e->getMessage();
            $logger->infoSend("NamingError");
            return $link->value;
        }
        return $result;
    }

    public static function getStats()
    {
        $data = Yii::$app->request->post();
        if (!self::checkData('label'))
            return ResponseComponent::getJson('error', 'Invalid data');
        $app = Apps::getApp($data['package']);
        $installs = self::getInstalls($app->id, $data['label']);
        $message = [
            'installs' => $installs['devices']
        ];
        return self::getJson('ok', $message);
    }

    public static function getLink()
    {
        $error = ResponseComponent::getJson('error', 'Invalid data');
        $request = Yii::$app->request->post();
        if (!self::checkData('link_id')) return $error;
        $link = Links::findOne($request['link_id']);
        if (!$link) return $error;
        return ResponseComponent::getJson('ok', $link);
    }

    private static function getLinkcountry($data)
    {
        $linkcountries = array();
        $geo = $data['geo'];
        $app = Apps::find()->where(['package' => $data['package']])->one();
        foreach ($geo as $country) {
            $linkcountry = Linkcountries::find()
                ->where(['country_code' => $country])
                ->andWhere(['app_id' => $app->id])
                ->andWhere(['archived' => 0])->one();
            if ($linkcountry == null) {
                $linkcountry = new Linkcountries();
                $linkcountry->app_id = $app->id;
                $linkcountry->country_code = $country;
                $linkcountry->archived = 0;
                if (!$linkcountry->save()) return false;
            }
            array_push($linkcountries, $linkcountry);
        }
        return $linkcountries;
    }

    public static function createLink($data, $user, $isMain = 0)
    {
        $errors = array();
        $linkcountries = self::getLinkcountry($data);
        if (!$linkcountries) return ResponseComponent::getJson('error', 'Invalid geo');
        foreach ($linkcountries as $linkcountry) {
	    $linkcountry->blockOrganic($user);
            $link = self::checkNaming($data, $linkcountry->country_code, $isMain);
            if (!$link) $link = new Links();
            $link->key = 'camp_name';
            $link->linkcountry_id = $linkcountry->id;
            $link->user_id = $user->id;
            $link->label = $link->value;
            $link->url = $data['url'];
            $link->archived = 0;
            $link->is_main = $isMain;
            if ($isMain) {
                $link->value = "Organic";
            } else {
                $link->value = $data['value'];
            }
            if (!$link->save()) {
                array_push($errors, $linkcountry->country_code);
            }
            //if (!$link->save()) return ResponseComponent::getJson('error', 'Internal error');
        }
        $naming = self::getCampName($link);
        $message['naming'] = $naming;
        if (count($errors) > 0) {
            if (count($errors) == count($linkcountries)) {
                return ResponseComponent::getJson('error', "Naming already exists");
            }
            $message['unavailable_geos'] = $errors;
        }
        return ResponseComponent::getJson('ok', $message);
    }

    private static function getInstalls($app_id, $label)
    {
        $stats = array(
            'price' => 0,
            'devices' => 0
        );
        $devices = Stats::getInstalls($app_id, $label);
        foreach ($devices as $device) {
            $countryPrice = $appPrices[$device['country_code']] ?? $appPrices['all'] ?? 0;
            $stats['price'] += ($countryPrice * $device['count']);
            $stats['devices'] += $device['count'];
        }
        return $stats;
    }

}
