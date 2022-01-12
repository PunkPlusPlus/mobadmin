<?php

namespace app\controllers;

use Yii;
use app\models\Apps;
use app\models\Devices;
use app\models\Visits;
use app\models\Linkcountries;
use app\models\Links;
use app\models\Params;
use app\components\CloakingComponent;
use app\basic\debugHelper;

class responseConfigOld
{
    private $response;


    public $urlName = "bonus";
    public $appIdName = "appid";
    public $deviceIdName = "id";
    public $extraName = "extra";
    public $errorName = "error";
    public $accessTokenName = "access_token";
    public $linkId = -1;

    public function __construct()
    {
        $this->response = [
            $this->urlName => "false",
            $this->extraName => []
        ];
    }

    public function setUrl($newUrl)
    {
        $this->response[$this->urlName] = $newUrl;
    }

    public function setAppId($newAppId)
    {
        $this->response[$this->appIdName] = $newAppId;
    }

    public function setDeviceId($newDeviceId)
    {
        $this->response[$this->deviceIdName] = $newDeviceId;
    }

    public function setExtra($newExtra)
    {
        $this->response[$this->extraName] = $newExtra;
    }

    public function setError($newError)
    {
        $this->response[$this->errorName] = $newError;
    }

    public function setAccessToken($newToken)
    {
        $this->response[$this->accessTokenName] = $newToken;
    }

    public function setLinkId($newLinkId)
    {
        $this->linkId = $newLinkId;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getUrl()
    {
        return $this->response[$this->urlName];
    }

    public function getLinkId()
    {
        return $this->linkId;
    }

    public function getDeviceId()
    {
        return $this->response[$this->deviceIdName];
    }

    public function getExtra()
    {
        return $this->response[$this->extraName];
    }

    public function getAppId()
    {
        return $this->response[$this->appIdName];
    }
}


class ApioldController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    public $freeAccessActions = ['auth'];

    //public $freeAccess = true;

    public function actionIndex()
    {
        $data = 'Invalid request';
        return $this->renderPartial('index', [
            'data' => $data
        ]);
    }

    function checkJsonData($jsonData)
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

    function isJSON($string)
    {
        return is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)));
    }

    function replaceVarUrl($device_deeplinks, $countryInfo, $jsonData)
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
        if (isset($jsonData['appsflyer_id'])) {
            $newUrl = str_replace("{appsflyer_device_id}", urlencode($jsonData['appsflyer_id']), $newUrl);
        }
        if (isset($jsonData['campaign_name'])) {
            $newUrl = str_replace("{campaign_name}", urlencode($jsonData['campaign_name']), $newUrl);
        }
        if (isset($jsonData['idfa'])) {
            $newUrl = str_replace("{idfa}", urlencode($jsonData['idfa']), $newUrl);
        }
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

    function parseDeepLinks($deeplink_string)
    {
        if (strpos($deeplink_string, "app://") !== false) {
            $deeplink_string = str_replace("app://", "", $deeplink_string);
            $paramsArr = "";

            $access_token = "";
            parse_str($deeplink_string, $paramsArr);
            return json_decode(json_encode($paramsArr));

            //debugHelper::print($deeplink_string, false);
            //debugHelper::print($paramsArr);
        }
    }

    public function doubleAuth($deviceData, $responseServer, $logger = -1)
    {
        //заносим повторный запуск в БД
        $cache = Yii::$app->cache;
        $package = $deviceData->package;
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

//        $secretKey = "7ZVUj6n!IYcl";
//        $method = "double_auth";
//        $cacheKeyAuth = $method . "-" . $package . "-" . $ip . "-" . $secretKey;
//        $data = $cache->get($cacheKeyAuth);
//        $logger->data['app']['package'] = $deviceData->package ?? $deviceData['package'];
//        $logger->data['cache']['key'] = $cacheKeyAuth;
//        $logger->data['cache']['available'] = $data ? true : false;

        $data = false;
        if (!$data) {
            $visit = Visits::getDb()->cache(function () use ($deviceData) {
                return Visits::find()->where(['device_id' => $deviceData->id])->one();
            }, 60); //кэш на 1 мин

            $newVisit = new Visits();
            foreach ($visit as $key => $value) {
                if ($key != "id" && $key != "date" && $key != "is_first") {
                    $newVisit[$key] = $value;
                }
            }
            $newVisit->save();
            $logJSON['request'] = (array)$deviceData;
            $responseServer->setUrl($visit->url);
//            $serverResponse = json_decode($visit['server_response'], true);
//            if(isset($serverResponse) && count($serverResponse) > 1){
//                $serverResponse = (array)$serverResponse[1] ?? (array)$serverResponse[0] ?? [];
//            }

            //$serverResponse['access_token'] = "";

            $logJSON['response'] = $responseServer->getResponse();

            $logger->data['user']['visit_id'] = $newVisit->id;
            $logger->data['device'] = $logJSON;
            $logger->infoSend("DeviceDoubleAuth");
//            $cache->set($cacheKeyAuth, $responseServer->getResponse(), 10);
            print json_encode($responseServer->getResponse());
            exit();
        } else {
            $logJSON['request'] = (array)$deviceData;
            $logJSON['response'] = $data;
            $logger->data['device'] = $logJSON;
            $logger->infoSend("DeviceDoubleAuth");

            print json_encode($data);
            exit();
        }
    }

    public function actionAuth()
    {
        $logger = new LogsController();

        $responseServer = new responseConfigOld;
        if (!isset($_REQUEST['data'])) {
            exit();
        }
        $deviceJson = $_REQUEST['data'];

        if (!isset($_GET['getonly'])) {
            //расшифровываем ответ из base64
            $deviceJson = base64_decode(substr($deviceJson, 0, 10) . substr($deviceJson, 14));
        } else {
            $logger->data['getonly'] = true;
        }
        $textError = [];

        //проверяем на валидность JSON
        if ($this->isJSON($deviceJson) && isset(json_decode($deviceJson)->package)) {
            $deviceDataOriginal = json_decode($deviceJson);
            $deviceData = $deviceDataOriginal;
            $idfa = $deviceDataOriginal->idfa ?? NULL;
            $appsflyerId = $deviceDataOriginal->appsflyer_device_id;

            if (isset($deviceData->id) && $deviceData->id != -1) {
                $this->doubleAuth($deviceData, $responseServer, $logger);
                exit();
            }

            //проверяем кэш запроса
//            $cache = Yii::$app->cache;

            $package = $deviceDataOriginal->package;
//            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
//            $secretKey = "7ZVUj6n!IYcl";
//            $cacheKeyAuth = $package . "-" . $ip . "-" . $secretKey;
//            $data = $cache->get($cacheKeyAuth);
//            $logger->data['cache']['key'] = $cacheKeyAuth;
//            $logger->data['cache']['available'] = $data ? true : false;
//            $logger->data['app']['package'] = $package;

            $data = false;
            if ($data) {
                $logJSON['request'] = (array)json_decode($deviceJson, true);
                $logJSON['response'] = $data;

                $logger->data['device'] = $logJSON;
                $logger->infoSend('DeviceAuth');

                return $this->renderPartial('index', [
                    'data' => json_encode($data)
                ]);
            }
            //end проверяем кэш устройства

            //находим нужное приложение по package
            $appInfo = Apps::find()->where(['package' => $deviceData->package])->one();
            if ($appInfo) {
                $responseServer->setAppId($appInfo->id);
                $deviceInfo = ['deviceModel' => null, 'deviceName' => null, 'deviceBrand' => null, 'resolution' => null];
                $allowedCountry = [];

                //находим все открытые страны на данном приложении
                $allCountryApp = Linkcountries::find()
                    ->where(['app_id' => $appInfo['id']])
                    ->andWhere(['archived' => 0])
                    ->all();

                foreach ($allCountryApp as $appCountry)
                    array_push($allowedCountry, $appCountry->country_code);

                $cloakingInfo = $this->cloaking($deviceInfo, $allowedCountry);
                $countryCode = $cloakingInfo['countryCode'];
                $filterLogId = $cloakingInfo['logId'];
                $isBot = $cloakingInfo['isBot'];

                $logger->data['cloaking']['country_code'] = $countryCode;
                $logger->data['cloaking']['is_bot'] = $isBot;

                //находим связь с страной
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
                $logger->data['link_country']['id'] = $countryInfo['id'];
                $logger->data['link_country']['country_code'] = $countryInfo['country_code'];

                if (!$isBot) {
                    $params = $this->getParams($appInfo['id'], -1, $countryInfo['id']);
                    $responseServer->setExtra($params);
                    $responseServer->setUrl("true");
                } else {
                    $responseServer->setUrl("false");
                }

                //проверка на существование данного устройства в базе
                $device = false;
                $is_first = true;
                if (isset($deviceData->id) && $deviceData->id != -1) {
                    $device = Devices::find()->where(['id' => $deviceData->id])->one();
                    $is_first = false;
                }


                //если не нашли то создаем пустую запись с девайсом
                if (!$device) $device = new Devices();
                $device->app_id = $appInfo->id;
                $device->idfa = $idfa;
                $device->appsflyer_id = $appsflyerId;
                $device->save();

                //находим все открытые страны на данном приложении
                $linksInfo = Links::find()
                    ->where(['linkcountry_id' => $countryInfo['id']])
                    ->andWhere(['is_main' => 1])
                    ->andWhere(['archived' => 0])
                    ->one();


                if (!$is_first) {
                    $visits = Visits::find()
                        ->where(['device_id' => $device->id])
                        ->one();
                    $linksInfo['id'] = $visits->link_id;
                    $access_token = "";
                } else {
                    $access_token = md5(time() . $device['id'] . rand(0, 99) . "uRZtC0YPhxOmfCa5UmhVOOtD79IDmU");
                }

                if ($isBot) $device->link_id = $linksInfo['id'];
                $device->save();

                $responseServer->setAccessToken($access_token);
                $responseServer->setDeviceId($device['id']);

                $extra[0] = json_decode($deviceJson);
                $server_response[0] = $responseServer->getResponse();

                $visits = new Visits();
                $visits->device_id = $responseServer->getDeviceId();
                if (count($responseServer->getExtra()) > 0) $visits->extra = json_encode($responseServer->getExtra());
                $visits->cloaking = $isBot ?? true ? 1 : 0;
                //$visits->server_response = json_encode($server_response);
                //$visits->extra = json_encode($extra);
                $visits->filterlog_id = $filterLogId;
                $visits->link_id = $linksInfo['id'];
                $visits->access_token = $access_token;
                $visits->is_first = $is_first ? 1 : 0;
                if (strlen($device->link_id) > 0) {
                    $visits->link_id = $device->link_id;
                }
                if (isset($_SERVER['SERVER_NAME'])) $visits->server_name = $_SERVER['SERVER_NAME'];
                $visits->save();
                $logger->data['user']['visit_id'] = $visits->id;
                //debugHelper::print($visits);
                //$cache->set($cacheKeyAuth, $responseServer->getResponse(), 120);
            } else {
                array_push($textError, 'Package not found.');
            }
        } else {
            array_push($textError, 'Invalid json format: ' . $deviceJson);
        }

        if (count($textError) > 0) {
            if ($_GET['debug'] ?? false) $responseServer->setError($textError);
            $logger->data['errors'] = $textError;
        }

        $logJSON['request'] = (array)json_decode($deviceJson, true);
        $logJSON['response'] = $responseServer->getResponse();

        $logger->data['device'] = $logJSON;
        $logger->infoSend('DeviceAuth');

        return $this->renderPartial('index', [
            'data' => json_encode($responseServer->getResponse())
        ]);
    }

    public function cloaking($deviceInfo, $allowCountry)
    {
        $disableFilter = [
            //'country',
            'traffarmor',
            //'blocking'
        ];

        return CloakingComponent::getInstance()->cloak($deviceInfo, $disableFilter, $allowCountry);
    }

    public function actionGetUrl()
    {
        $logger = new LogsController();
        $responseServer = new responseConfigOld;
        $data = $_POST['data'] ?? $_GET['data'] ?? false;
        $textError = [];

        try {
            $dataJson = json_decode($data);
            $access_token = $dataJson->access_token;
        } catch (\Exception $e) {
            array_push($textError, 'json is not vaild');
            $logger->data['errors'] = $textError;
            $logger->errorSend("DeviceGetUrl");
            print_r(json_encode($textError));
            exit();
        }

        //проверяем кэш запроса
        /*
        $cache = Yii::$app->cache;
        $secretKey = "7ZVUj6n!IYcl";
        $cacheKeyAuth = $access_token . "-" . $secretKey;
        $data = $cache->get($cacheKeyAuth);
        $logger->data['cache']['key'] = $cacheKeyAuth;
        $logger->data['cache']['available'] = $data ? true : false;
        */
        $data = false;
        if (!$data) {
            $selectVisit = Visits::find()->where(['access_token' => $access_token])->one();

            if ($selectVisit) {
                if ($selectVisit->cloaking == 0) {
                    $appInfo = Apps::find()->where(['id' => $selectVisit->devices->app_id])->one();

                    $logger->data['app']['package'] = $appInfo['package'];

                    $trafficRoute = $appInfo['traffic_route'];
                    $appIdOriginal = $selectVisit->devices->app_id;
                    if ($trafficRoute == 0) {
                        $appId = $appIdOriginal;
                    } else {
                        $appId = \Yii::$app->params['traffic_route_app_id'];
                    }

                    if($appId !== \Yii::$app->params['traffic_route_app_id'] && $trafficRoute != 1) {
                        $countryInfo = self::getCountryInfo($selectVisit, $appId);

                        $linksInfo = Links::find()
                            ->where(['linkcountry_id' => $countryInfo['id']])
                            ->andWhere(['is_main' => 1])
                            ->andWhere(['archived' => 0])
                            ->one();

                        $trafficRoute = BalanceController::changeBalance($linksInfo['user_id'], $appId, $countryInfo['country_code']);

                        if ($trafficRoute == 1) {
                            $appId = \Yii::$app->params['traffic_route_app_id'];
                        }
                    }

                    //$responseServer->setUrl(json_decode($selectVisit->server_response, true)[$responseServer->urlName]);

                    $dataCampAf = (array)$dataJson;
                    if (isset($dataCampAf['campaign_af:']) || isset($dataJson->campaign_af)) {
                        if(isset($dataCampAf['campaign_af:']))
                            $campaign_af = $dataCampAf['campaign_af:'];
                        else
                            $campaign_af = $dataJson->campaign_af;

                        $separatorList = ["~", "/", "|", ";"];
                        for ($i = 0; $i < count($separatorList); $i++) {
                            if(!isset($separator)) {
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


                        if (isset($separator)) {
                            $subList = explode($separator, $campaign_af);
                            $deeplink = "app://camp_name=";
                            for($i=0;$i<count($subList);$i++){
                                if($i==0){
                                    $deeplink .= $subList[$i];
                                }else{
                                    $deeplink .= "&sub_".$i."=".$subList[$i];
                                }
                            }
                        }else{
                            $deeplink = "app://camp_name=".$campaign_af;
                        }
                        $deeplink = $this->parseDeepLinks($deeplink);
                    }
                    //если есть диплинка то парсим её в массив
                    if (isset($dataJson->deeplink) && strlen($dataJson->deeplink) > 0) $deeplink = $this->parseDeepLinks($dataJson->deeplink);
                    if (isset($dataJson->deeplink) && isset($dataJson->deeplink_fb) && strlen($dataJson->deeplink) <= 0 && strlen($dataJson->deeplink_fb) > 0) $deeplink = $this->parseDeepLinks($dataJson->deeplink_fb);

                    $countryInfo = self::getCountryInfo($selectVisit, $appId);
                    $linkInfo = Links::find()
                        ->where(['linkcountry_id' => $countryInfo['id']])
                        ->andWhere(['is_main' => 1])
                        ->andWhere(['archived' => 0])
                        ->one();

                    $logger->data['link_country']['id'] = $countryInfo['id'];
                    $logger->data['link_country']['country_code'] = $countryInfo['country_code'];
                    $logger->data['link']['id'] = $linkInfo['id'];

                    $jsonData = [];
                    $jsonData = json_decode(json_encode($jsonData));

                    if (isset($selectVisit->devices->appsflyer_id)) {
                        $jsonData['appsflyer_id'] = $selectVisit->devices->appsflyer_id;
                    }
                    if (isset($selectVisit->devices->idfa)) {
                        $jsonData['idfa'] = $selectVisit->devices->idfa;
                    }
                    if (isset($campaign_af)) {
                        $jsonData['campaign_name'] = $campaign_af;
                    }
                    $newUrlOption = $this->replaceVarUrl($deeplink ?? false, $countryInfo, $jsonData);

                    $responseServer->setLinkId($newUrlOption['link_data']['id']);
                    $responseServer->setUrl($newUrlOption['url']);

                    switch ($trafficRoute) {
                        case 1:
                            $params = $this->getParams($appIdOriginal, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id']);
                            $paramsNew = $this->getParams($appId, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id']);
                            $extraMain = [];

                            foreach ($params as $key => $value)
                                $extraMain[$key] = $value;

                            foreach ($paramsNew as $key => $value)
                                $extraMain[$key] = $value;

                            $params = $extraMain;
                            break;
                        default:
                            $params = $this->getParams($appId, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id']);
                            break;
                    }
                    $params['pid'] = $newUrlOption['pid'];

                    $responseServer->setExtra($params);

//                    $serverResponse = json_decode($selectVisit->server_response);
//                    $serverResponse[1] = $responseServer->getResponse();
//
//                    $extraResponse = json_decode($selectVisit->extra);
//                    $extraResponse[1] = $dataJson;

                    $selectVisit->link_id = $responseServer->getLinkId();
                    $selectVisit->access_token = null;
                    $selectVisit->url = $responseServer->getUrl();
                    try {
			$selectVisit->campaign_af = $campaign_af;

		    } catch (\Exception $e) {
			$selectVisit->campaign_af = null;
		    }
                    
                    //$selectVisit->server_response = json_encode($serverResponse);
                    if (isset($dataJson->deeplink) && strlen($dataJson->deeplink) > 0) {
                        $selectVisit->deeplink = $dataJson->deeplink;
                    }
                    //$selectVisit->extra = json_encode($extraResponse);
                    $selectVisit->save();

                    $selectDevice = Devices::find()->where(['id' => $selectVisit['device_id']])->one();
                    $selectDevice->link_id = $responseServer->getLinkId();
                    $selectDevice->app_id = $appId;
                    $selectDevice->save();

                    //убираем параметр "code" вторым запросом (Для уменьшения времени загрузки)
                    $extra = $responseServer->getExtra();
                    unset($extra['code']);
                    $responseServer->setExtra($extra);
                    //end убираем параметр "code" вторым запросом


                    $logJSON['request'] = (array)$dataJson;
                    $logJSON['response'] = $responseServer->getResponse();
                    $logger->data['device'] = $logJSON;
                    $logger->data['device']['id'] = $selectVisit->device_id;
                    $logger->data['user']['visit_id'] = $selectVisit->id;
                    $logger->infoSend("DeviceGetUrl");

//                    $cacheData['visit_id'] = $selectVisit->id;
//                    $cacheData['response'] = $responseServer->getResponse();
//                    $cache->set($cacheKeyAuth, $cacheData, 160);

                    return json_encode($responseServer->getResponse());
                } else {
                    array_push($textError, 'false');
                }
            } else {
                array_push($textError, 'visits not found');
            }

            if (count($textError) > 0) {
                if ($_GET['debug'] ?? false) $responseServer->setError($textError);
                $logger->data['errors'] = $textError;
                $logger->errorSend("DeviceGetUrl");

                print_r(json_encode($textError));
                exit();
            }
        } else {
            $logJSON['request'] = (array)$dataJson;
            $logJSON['response'] = $data;
            $logger->data['device'] = $logJSON;
            $logger->data['user']['visit_id'] = $data['visit_id'];
            $logger->infoSend("DeviceGetUrl");
            print json_encode($data['response']);
            exit();
        }
    }

    public function getParams($app_id, $user_id, $linkcountry_id)
    {
        $extraForReturn = [];
        $params = Params::find()
            ->where(['app_id' => $app_id])
            ->orWhere(['user_id' => $user_id])
            ->orWhere(['linkcountry_id' => $linkcountry_id])
            ->andWhere(['archived' => 0])
            ->all();

        $connection = Yii::$app->getDb();
        $params = $connection->createCommand("
            SELECT
                *
            FROM
                tbl_params
            WHERE
                ( tbl_params.app_id = :app_id AND tbl_params.user_id = :user_id AND tbl_params.linkcountry_id = :linkcountry_id ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = -1 AND tbl_params.linkcountry_id = :linkcountry_id ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = :user_id AND tbl_params.linkcountry_id = -1 ) 
                OR ( tbl_params.app_id = :app_id AND tbl_params.user_id = -1 AND tbl_params.linkcountry_id = -1 ) 
                AND tbl_params.archived = 0
            ORDER BY tbl_params.user_id, tbl_params.linkcountry_id ASC
        ", [':app_id' => $app_id, ':user_id' => $user_id, ':linkcountry_id' => $linkcountry_id]);
        $params = $params->queryAll();

        if ($params) {
            $extraForReturn = [];
            foreach ($params as $param) {
                $extraForReturn[$param['key']] = $param['value'];
            }
        }

        return $extraForReturn;
    }

    public function actionTestAuth()
    {
        //1 проверка с клоакой "не прошел" и 1 проверка с клоакой "прошел"
        $stats = [
            "auth_with_cloak" => "error",
            "auth_without_cloack" => "error",
        ];
        $data = '{"package":"com.aflagroup.test","id":"-1","appsflyer_device_id":"1596682771401-5776124448088363778"}';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://aflagroupdev.profitnetwork.app/api/auth?getonly=true&data=" . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $outputJson = json_decode($output, true);

        if (isset($outputJson['bonus'])) {
            if ($outputJson['bonus'] === "false") {
                if (isset($outputJson['bonus']) && isset($outputJson['id']) && isset($outputJson['access_token'])) {
                    $stats['auth_with_cloak'] = "completed";
                    $stats['access_token'] = $outputJson['access_token'];
                }
            } else {
                if (isset($outputJson['bonus']) && isset($outputJson['id']) && isset($outputJson['access_token'])) {
                    $stats['auth_without_cloack'] = "completed";
                    $stats['access_token'] = $outputJson['access_token'];
                }
            }
        }
        //debugHelper::print($output);
        if ($stats['auth_with_cloak'] == "error" && $stats['auth_without_cloack'] == "error") {
            debugHelper::print($output);
        } else {
            header('Location: ' . "/api/test-geturl?stats=" . json_encode($stats));
            die();
        }
    }

    public function actionTestGeturl($stats)
    {
        $stats = json_decode($stats, true);
        $stats['two_request_with_cloak'] = "error";
        $stats['two_request_without_cloack'] = "error";
        $stats['url_device'] = "error";
        $stats['deep_link'] = "error";

        $data = '{"access_token":"' . $stats['access_token'] . '","deeplink":"app://p1=1","conversion_data":{"status":"failure","type":"onInstallConversionFailure","data":{"0":"E","1":"r","2":"r","3":"o","4":"r","5":" ","6":"c","7":"o","8":"n","9":"n","10":"e","11":"c","12":"t","13":"i","14":"o","15":"n","16":" ","17":"t","18":"o","19":" ","20":"s","21":"e","22":"r","23":"v","24":"e","25":"r","26":":","27":" ","28":"4","29":"0","30":"0"}}}';
        $data = urlencode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://aflagroupdev.profitnetwork.app/api/get-url?getonly=true&data=" . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $outputJson = json_decode($output, true);


        if (isset($outputJson['bonus'])) {
            if ($outputJson['bonus'] === "false") {
                if (isset($outputJson['bonus'])) {
                    $stats['two_request_with_cloak'] = "completed";
                }
            } else {
                if (isset($outputJson['bonus'])) {
                    $stats['two_request_without_cloack'] = "completed";
                }
            }
            $stats['url_device'] = "error";
        }

        debugHelper::print($stats);
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

    public function actionRegister()
    {
        $post_data_arr = ['firstname'=>'firstname',
            'lastname'=>'lastname',
            'email'=>'email',
            'password'=>'password',
            'phone'=>'phone',
            'phonecc'=>'phonecc',
            'partner_id'=>'partner_id',
            'click_id'=>'click_id',
            'country'=>'country',
            'site'=>'site',
            'user_id'=>'user_id',
            'key'=>'key',
            'affid'=>'affid',
            'payout'=>'payout',
            'link_type'=>'link_type',
            'typu'=>'typu',
            'typc'=>'typc',
            'prelanding'=>'prelanding',
            'convurl'=>'convurl',
            'referer'=>'referer',
            'cookiehash'=>'cookiehash',
            'pixel_id'=>'pixel_id',
            'access_token'=>'access_token',
            't3'=>'t3',
            'campid'=>'campid',
            'funnel'=>'funnel',
            'hoster'=>'hoster',
            'llbnbd'=>'llbnbd',
            'product_key'=>'product_key',
            'googleua'=>'googleua'];

        $data = [];
        foreach($post_data_arr as $key=>$subKey) {
            if(isset($_POST[$key])) {
                $data[$key]=$_POST[$key];
            }
        }

        $post_data = http_build_query($data);
        ////////////////////////////////////////////

        $sourceHeader = [
            'HTTP_CLIENT_IP'=>'X-Forwarded-For',
            'HTTP_X_FORWARDED_FOR'=>'X-Forwarded-For',
            'REMOTE_ADDR'=>'X-Forwarded-For',
        ];
        $header = [];
        foreach($sourceHeader as $key=>$subKey) {
            if (!empty($_SERVER[$key])){
                $header[] = sprintf('%s: %s',$subKey, $_SERVER[$key]);
            }
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $referer = $_SERVER['HTTP_REFERER'] ?? ((isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']);
        ///////////////////////////////////////////

        $ch = curl_init('https://aflagroup.link/form/i/forms/register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $content = curl_exec($ch);

        if($this->isJSON($content)) {
            $response = json_decode($content, true);
            $response['url'] = $response['result'];
            $response['message'] = $response['newPassword'];
            $response['status'] = 201;
            unset($response['result']);
            unset($response['newPassword']);
        } elseif (strpos($content, 'http') !== false) {
            $response = [
                'url' => $content,
                'status' => 200
            ];
        } else {
            $response = [
                'url' => false,
                'status' => 400,
                'message' => $content
            ];
        }

        return json_encode($response);
    }
}
