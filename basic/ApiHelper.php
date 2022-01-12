<?php


namespace app\basic;


use app\components\CloakingComponent;
use app\models\Linkcountries;
use app\models\Links;
use app\models\Visits;
use Yii;
use app\controllers\LogsController;

class ApiHelper
{
    public static function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip);
            $ip = trim($ip[0]);
        }

        return $ip;
    }

    public static function validateUrl($url)
    {
        // if(stripos($url, 'block' !== false)) {
        //     $url = 'block';
        // }
        // return $url;

        if (stripos($url, "://") !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function parseDeepLinks($deeplink_string)
    {
        if (strpos($deeplink_string, "app://") !== false) {
            $deeplink_string = str_replace("app://", "", $deeplink_string);
            $paramsArr = "";

            parse_str($deeplink_string, $paramsArr);
            return json_decode(json_encode($paramsArr));
        }
        return false;
    }

    public static function replaceVarUrl($device_deeplinks, $countryInfo, $jsonData)
    {
        $linksInfo = Links::find()
            ->where(['linkcountry_id' => $countryInfo['id']])
            ->andWhere(['is_main' => 1])
            ->andWhere(['archived' => 0])
            ->one();
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

        if ($device_deeplinks) {
            $linksInfo = Links::find()
                ->where(['linkcountry_id' => $countryInfo['id']])
                ->andWhere(['archived' => 0])
                ->all();
            if ($linksInfo) {
                foreach ($linksInfo as $link) {
                    foreach ($device_deeplinks as $refererKey => $refererValue) {
                        if ($link->key == $refererKey && $link->value == $refererValue) {
                            $linkData = $link;
                            $newUrl = $link->url;
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

            }
        }


        //$newUrl = str_replace("{visit_id}", urlencode($visitInfo['id']), $newUrl);
        //$newUrl = str_replace("{app_id}", urlencode($appInfo['id']), $newUrl);
        //$newUrl = str_replace("{app_name}", urlencode($appInfo['name']), $newUrl);
        //debugHelper::print($jsonData['appsflyer_device_id']);

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

//        if (isset($jsonData['appsflyer_id'])) {
//            $newUrl = str_replace("{appsflyer_device_id}", urlencode($jsonData['appsflyer_id']), $newUrl);
//        }
//        if (isset($jsonData['campaign_name'])) {
//            $newUrl = str_replace("{campaign_name}", urlencode($jsonData['campaign_name']), $newUrl);
//        }
//        if (isset($jsonData['idfa'])) {
//            $newUrl = str_replace("{idfa}", urlencode($jsonData['idfa']), $newUrl);
//        }
        $newUrl = str_replace("{country}", urlencode($countryInfo['country_code']), $newUrl);
        if (strlen($replaceFullUrl) > 0)
            $newUrl = $replaceFullUrl;

        $response = [
            'url' => $newUrl,
            'link_data' => $linkData,
            'pid' => $pid
        ];

        return $response;
    }

    public static function isJSON($string)
    {
        return is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)));
    }

    public static function checkJsonData($jsonData)
    {
        $fields = [
            'package',
            'device_model',
            'device_name',
            'device_brand',
            'device_uid',
            'device_resolution',
            'device_lang'
        ];

        foreach ($jsonData as $key => $value) {
            foreach ($fields as $field => $fieldValue) {
                if (isset($fields[$key])) {
                    $fields[$key] = true;
                }
            }
        }

        $isTrust = true;
        foreach ($fields as $field => $fieldValue) {
            if (!$fieldValue) {
                $isTrust = false;
                break;
            }
        }

        return $isTrust;
    }

    public static function getParams($app_id, $user_id, $linkcountry_id, $for_bot = 0) : array
    {
        $extraForReturn = [];

        $connection = Yii::$app->getDb();
        $params = $connection->createCommand("
            SELECT
                *
            FROM
                tbl_params
            WHERE
                ( tbl_params.app_id = :app_id AND tbl_params.user_id = :user_id AND tbl_params.linkcountry_id = :linkcountry_id AND tbl_params.is_for_bot = :is_for_bot) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = -1 AND tbl_params.linkcountry_id = :linkcountry_id AND tbl_params.is_for_bot = :is_for_bot ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = :user_id AND tbl_params.linkcountry_id = -1 AND tbl_params.is_for_bot = :is_for_bot ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = -1 AND tbl_params.linkcountry_id = -1 AND tbl_params.is_for_bot = :is_for_bot ) 
                AND tbl_params.archived = 0
            ORDER BY tbl_params.user_id, tbl_params.linkcountry_id ASC
        ", [':app_id' => $app_id, ':user_id' => $user_id, ':linkcountry_id' => $linkcountry_id, ':is_for_bot' => $for_bot]);
        $params = $params->queryAll();

        if ($params) {
            foreach ($params as $param) {
                $extraForReturn[$param['key']] = $param['value'];
            }
        }

        return $extraForReturn;
    }

    public static function getCountryInfo($selectVisit, $appId)
    {
        $allCountryApp = Linkcountries::find()
            ->where(['app_id' => $appId])
            ->andWhere(['archived' => 0])
            ->all();

        //находим связь с страной
        $countryInfo = false;
        foreach ($allCountryApp as $countryApp) {
            if ($countryApp->country_code == "all") {
                $countryInfo = $countryApp;
            }
        }

        foreach ($allCountryApp as $countryApp) {
            if ($countryApp->country_code == strtolower($selectVisit->filterlog->country)) {
                $countryInfo = $countryApp;
            }
        }
        return $countryInfo;
    }

    public static function cloaking($deviceInfo, $allowCountry, $blockByList)
    {
        // комментировать country для продакшена!!!
        $disableFilter = [
            //'country',
            'traffarmor',
            //'blocking'
        ];

        return CloakingComponent::getInstance()->cloak($deviceInfo, $disableFilter, $allowCountry, $blockByList);
    }

    public static function getAppId($selectVisit)
    {
        try {
            $json = $selectVisit->extra;
            $array = json_decode($json, true);
            $app_id = $array['one_signal_key'];
            return $app_id;
        } catch (\Exception $e) {
            return false;
        }
    }
   
    public static function sendOnesignal($tags, $player_id, $app_id)
    {
        $logger = new LogsController();
        $logger->data['message']['tags'] = json_encode($tags);
        $logger->data['message']['user_id'] = $player_id;
        $logger->data['message']['app_id'] = $app_id;
        $fields = array(
            'app_id' => $app_id,
            'tags' => $tags
        );
        $fields = json_encode($fields);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::$app->params['onesignal_base_url'].$player_id);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $logger->data['message']['response'] = $response;
            curl_close($ch);
        } catch (\Exception $e) {
            $logger->data['message']['response'] = "Sending aborted";
            $logger->data['message']['error'] = $e->getMessage();
            $logger->warningSend('SendOnesignal');
            return false;
        }
        $logger->infoSend('SendOnesignal');
        return $response;
    }

 
    public static function validateRequest($data)
    {
        $data = base64_decode($data);
//      $dataJson = $data;

        try {
            $dataJson = json_decode($data);
            if (isset($dataJson->loaderror)) {
                $err_log = new LogsController();
                $err_log->data['message'] = $dataJson->loaderror;
                $err_log->warningSend("LoadError");
            }
            $visitId = $dataJson->visitId;
            $onesignal_id = $dataJson->onesignal_id;
            $visit = Visits::find()->where(['id' => $visitId])->one();
            if (!isset($dataJson->access_token) && $dataJson->access_token != $visit->access_token) {
                return false;
            }
            return ['visitId' => $visitId, 'onesignal_id' => $onesignal_id];
        } catch (\Exception $e) {
            return false;
        }
    }

    
}
