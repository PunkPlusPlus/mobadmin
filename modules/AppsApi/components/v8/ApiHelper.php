<?php

namespace app\modules\AppsApi\components\v8;

use app\components\CloakingComponent;
use app\controllers\LogsController;
use app\models\Apps;
use app\models\Devices;
use app\modules\AppsApi\models\Blacklist;
use app\components\BlackListComponent;
use Yii;

class ApiHelper extends \app\basic\ApiHelper
{
    public static function checkBlackList($appInfo, $idfa)
    {
        $status = $appInfo->published;
        if ($status != 4 && $status != 3 && $status != -1) {
            $check = Blacklist::getItem($idfa);
            if (!$check) $check = BlackListComponent::addToList($idfa, 1);
        } else {
            $check = Blacklist::getBannedItem($idfa);
        }
        $blockByList = $check->block ?? false;
        return $blockByList;
    }

    public static function getCountryInfo($allCountryApp, $countryCode)
    {
        $countryInfo = false;
        foreach ($allCountryApp as $countryApp) {
            if ($countryApp->country_code == 'all') {
                $countryInfo = $countryApp;
            }
        }
        foreach ($allCountryApp as $countryApp) {
            if ($countryApp->country_code == strtolower($countryCode)) {
                $countryInfo = $countryApp;
            }
        }
        return $countryInfo;
    }

    public static function botCheck($isBot, $countryInfo, $appInfo)
    {
        $params = self::getParams($appInfo['id'], -1, $countryInfo['id'] ?? -1, 1);
        if ($isBot) {
            return $params;
        } else {
            // ПЕРЕНАПРАВЛЕНИЕ
            $params = array_merge($params, self::getParams($appInfo['id'], -1, $countryInfo['id'] ?? -1));

            if ($appInfo->traffic_route) {
                $traffic_route_app_id = \Yii::$app->params['traffic_route_app_id'];
                $params = array_merge($params, self::getParams($traffic_route_app_id, -1, $countryInfo['id'] ?? -1));
            }
        }
        return $params;
    }

    public static function botCheckNaming($isBot, $appInfo)
    {
        $params = self::getNamingParams($appInfo['id'], -1, 1);
        if ($isBot) {
            return $params;
        } else {
            // ПЕРЕНАПРАВЛЕНИЕ
            $params = array_merge($params, self::getNamingParams($appInfo['id'], -1));

            if ($appInfo->traffic_route) {
                $traffic_route_app_id = \Yii::$app->params['traffic_route_app_id'];
                $params = array_merge($params, self::getNamingParams($traffic_route_app_id, -1));
            }
        }
        return $params;
    }

    public static function getNamingParams($app_id, $user_id, $for_bot = 0): array
    {
        $extraForReturn = [];

        $connection = Yii::$app->getDb();
        $params = $connection->createCommand("
            SELECT
                *
            FROM
                tbl_params
            WHERE
                ( tbl_params.app_id = :app_id AND tbl_params.user_id = :user_id AND tbl_params.is_for_bot = :is_for_bot) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = -1 AND tbl_params.is_for_bot = :is_for_bot ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = :user_id AND tbl_params.linkcountry_id = -1 AND tbl_params.is_for_bot = :is_for_bot ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = -1 AND tbl_params.linkcountry_id = -1 AND tbl_params.is_for_bot = :is_for_bot ) 
                AND tbl_params.archived = 0
            ORDER BY tbl_params.user_id, tbl_params.linkcountry_id ASC
        ", [':app_id' => $app_id, ':user_id' => $user_id, ':is_for_bot' => $for_bot]);
        $params = $params->queryAll();

        if ($params) {
            foreach ($params as $param) {
                $extraForReturn[$param['key']] = $param['value'];
            }
        }
        $logger = new LogsController();
        $logger->data['message'] = $extraForReturn;
        $logger->infoSend("GetParams");

        return $extraForReturn;
    }

    public static function deviceCheck($isBot, $linksInfo, $request, $is_first)
    {
        $id = $request->id ?? null;
        $appInfo = Apps::getApp($request->package);
        //проверка на существование данного устройства в базе
        $device = false;
        if (!$is_first) {
            $device = Devices::find()->where(['id' => $id])->one();
        }

        //если не нашли то создаем пустую запись с девайсом
        if (!$device) $device = new Devices();
        $device->app_id = $appInfo->id;
        $device->idfa = $request->idfa ?? null;
        $device->appsflyer_id = $request->appsflyer_device_id ?? null;
        if ($isBot) $device->link_id = $linksInfo['id'] ?? false;
        $device->save();

        return $device;
    }

    public static function checkLink($linksInfo, &$isBot)
    {
        $logger = new LogsController();

        if (!$linksInfo) {
            $logger->data['message'] = "noLink";
            $isBot = true;
            ResponseConfig::getInstance()->setUrl(false);
        } else {
            $logger->data['message'] = "link";
        }
        $logger->infoSend("CheckLink");
    }

    public static function generateToken($device)
    {
        $access_token = md5(time() . $device['id'] . rand(0, 99) . "uRZtC0YPhxOmfCa5UmhVOOtD79IDmU");
        return $access_token;
    }

    public static function detectSeparator($dataJson)
    {
        $namingLog = new LogsController();
        $dataCampAf = (array)$dataJson;
        $namingLog->data['dataCampAf'] = $dataCampAf;
        if (isset($dataCampAf['campaign_af:']) || isset($dataJson->campaign_af)) {
            if (isset($dataCampAf['campaign_af:']))
                $campaign_af = $dataCampAf['campaign_af:'];
            else
                $campaign_af = $dataJson->campaign_af;
            $namingLog->data['campaign_af'] = $campaign_af;
            //$campaign_af = $campaign;
            $separatorList = ["~", "/", "|", ";", "_"];
            for ($i = 0; $i < count($separatorList); $i++) {
                if (!isset($separator)) {
                    $isSeparator = strpos($campaign_af, $separatorList[$i]);
                    //debugHelper::print($i." - ".$isSeparator, false);

                    if ($isSeparator === false) {
                        //разделитель не найден
                    } else {
                        //разделитель найден
                        $separator = $separatorList[$i];
                    }
                }
            }
            return $separator ?? false;
        }
        return false;
    }

    public static function getTrackUrl($visits, $redirectHash)
    {
        return 'https://' . $_SERVER['HTTP_HOST'] . '/api/v8/open-url?visitid=' . $visits->id . '&hash=' . $redirectHash;
    }

    public static function replaceNamingUrl($device_deeplinks, $linksInfo, $jsonData)
    {
        //$linksInfo = Links::getMainLink($countryInfo);
        $pid = $linksInfo->user_id;
        $linkData = $linksInfo;
        $newUrl = $linksInfo->url;
        $replaceFullUrl = "";

        if (isset($linkInfo->deeplinks) && $device_deeplinks) {
            $deepLinksArr = json_decode($linkInfo->deeplinks);
            foreach ($deepLinksArr as $deepKey => $deepValue) {
                foreach ($deepValue as $key => $value) {
                    foreach ($device_deeplinks as $refererKey => $refererValue) {
                        if ($deepKey == $refererKey && $key == $refererValue) {
                            $newUrl = $value;
                        }
                    }
                }
            }
        }

        if (isset($device_deeplinks) && count((array)$device_deeplinks) > 0) {
            try {
                foreach ($device_deeplinks as $key => $value) {
                    if ($key == "openurl") $replaceFullUrl = $value;
                    $newUrl = str_replace("{" . $key . "}", $value, $newUrl);
                }
            } catch (\Exception $e) {
                $logger = new LogsController();
                $logger->data['message']['error'] = $e->getMessage();
                $logger->infoSend("ReplaceError");
            }
        }

        $dict = [
            "appsflyer_device_id" => "{appsflyer_device_id}",
            "appsflyer_id" => "{appsflyer_device_id}",
            "campaign_name" => "{campaign_name}",
            "idfa" => "{idfa}",
            "package" => "{package_name}",
            "campaign_af" => "{naming}",
            "deeplink" => "{naming}"
        ];

        foreach ($dict as $key => $value) {
            if (isset($jsonData[$key])) {
                $newUrl = str_replace($value, urlencode($jsonData[$key]), $newUrl);
            }
        }

//        if (isset($jsonData['appsflyer_device_id'])) {
//            $newUrl = str_replace("{appsflyer_device_id}", urlencode($jsonData['appsflyer_id']), $newUrl);
//        }
//        if (isset($jsonData['campaign_name'])) {
//            $newUrl = str_replace("{campaign_name}", urlencode($jsonData['campaign_name']), $newUrl);
//        }
//        if (isset($jsonData['idfa'])) {
//            $newUrl = str_replace("{idfa}", urlencode($jsonData['idfa']), $newUrl);
//        }
        //$newUrl = str_replace("{country}", urlencode($countryInfo['country_code']), $newUrl);
        if (strlen($replaceFullUrl) > 0)
            $newUrl = $replaceFullUrl;

        $response = [
            'url' => $newUrl,
            'link_data' => $linkData,
            'pid' => $pid
        ];

        return $response;
    }

    public static function cloaking($deviceInfo, $allowCountry, $blockByList, $organic = true)
    {
        if ($organic) {
            // комментировать country для продакшена!!!
            $disableFilter = [
                //'country',
                'traffarmor',
                //'blocking'
            ];
        } else {
            $logger = new LogsController();
            $logger->data['message'] = "disable_filter";
            $logger->infoSend('CLOAKING');
            // комментировать country для продакшена!!!
            $disableFilter = [
                'country',
                'traffarmor',
                //'blocking'
            ];
        }
        return CloakingComponent::getInstance()->cloak($deviceInfo, $disableFilter, $allowCountry, $blockByList);
    }

    public static function isFirst($deviceData)
    {
        $device = false;
        $is_first = true;
        if (isset($deviceData->id) && $deviceData->id != -1) {
            $is_first = false;
        }
        return $is_first;
    }

}
