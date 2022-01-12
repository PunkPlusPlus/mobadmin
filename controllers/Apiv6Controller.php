<?php

namespace app\controllers;

use app\basic\ApiHelper;
use app\basic\debugHelper;
use app\models\Apps;
use app\models\Devices;
use app\models\Visits;
use app\models\Linkcountries;
use app\models\Links;
use app\components\BlackListComponent;
use app\models\Blacklist;


class responseConfig
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
            $this->urlName => false,
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


class Apiv6Controller extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    public $freeAccessActions = ['auth'];

    public function actionIndex()
    {
        $data = 'Invalid request';
        return $this->renderPartial('@app/views/apiv6/index', [
            'data' => $data
        ]);
    }

    public function actionAuth()
    {
        $logger = new LogsController();

        $responseServer = new responseConfig;
        if (!isset($_REQUEST['data'])) {
            exit();
        }
        $deviceJson = $_REQUEST['data'];

        if (!isset($_GET['getonly'])) {
            //расшифровываем ответ из base64
            $deviceJson = base64_decode($deviceJson);
        } else {
            $logger->data['getonly'] = true;
        }
        $textError = [];

        //проверяем на валидность JSON
        if (ApiHelper::isJSON($deviceJson) && isset(json_decode($deviceJson)->package)) {
            $deviceDataOriginal = json_decode($deviceJson);
            $deviceData = $deviceDataOriginal;
            $idfa = $deviceDataOriginal->idfa ?? NULL;
            $appsflyerId = $deviceDataOriginal->appsflyer_device_id;
            if (isset($deviceData->id) && $deviceData->id != -1) {
                $this->doubleAuth($deviceData, $responseServer, $logger);
                exit();
            }

            $package = $deviceDataOriginal->package;

            $logger->data['app']['package'] = $package;

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

                //@@ check black list
	         $log = new LogsController();
                 $log->data['message']['app'] = $appInfo->package;
                 if ($appInfo->published != 4 && $appInfo->published != 3 && $appInfo->published != -1) {
		     $log->data['message']['if'] = "is banned";
		     try {
		         $log->data['message']['status'] = $appInfo->published;
		     } catch (\Exception $e) {
		         $log->data['message']['status_error'] = "status is null";
		     } 		     		    
                     $check = Blacklist::find()->where(['idfa' => $idfa])->one();
                     if ($check == null) {
                         $check = BlackListComponent::addToList($idfa, 1);
		     }
                     $blockByList = $check->block ?? false;
                 } else {
		     
                     $check = Blacklist::find()->where(['idfa' => $idfa])->andWhere(['block' => true])->one();
                     $blockByList = $check->block ?? false;
		     $log->data['message']['check'] = json_encode($check);
                 }
		 $log->data['message']['block'] = $blockByList;
		 $log->infoSend('BlackList');
                //@@ end check black list

                $cloakingInfo = ApiHelper::cloaking($deviceInfo, $allowedCountry, $blockByList);
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

                $params = ApiHelper::getParams($appInfo['id'], -1, $countryInfo['id'], 1);
                
                if ($isBot) {
                    $responseServer->setExtra($params);
                    $responseServer->setUrl(false);
                } else {
                    // ПЕРЕНАПРАВЛЕНИЕ
                    $params = array_merge($params, ApiHelper::getParams($appInfo['id'], -1, $countryInfo['id']));

                    if($appInfo->traffic_route) {
                        $traffic_route_app_id = \Yii::$app->params['traffic_route_app_id'];
                        $params = array_merge($params, ApiHelper::getParams($traffic_route_app_id, -1, $countryInfo['id']));
                    }


                    $responseServer->setExtra($params);
                    $responseServer->setUrl(true);
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

                if (count($responseServer->getExtra()) > 0) {
                    $visits->extra = json_encode($responseServer->getExtra());
                } else {
                    $responseServer->setExtra('{}');
                }

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

        return $this->renderPartial('@app/views/apiv6/index', [
            'data' => json_encode($responseServer->getResponse())
        ]);
    }

    public function actionGetUrl()
    {
        $logger = new LogsController();
        $responseServer = new responseConfig;
        $data = $_POST['data'] ?? $_GET['data'] ?? false;
        $data = base64_decode($data);

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


        $selectVisit = Visits::find()->where(['access_token' => $access_token])->one();
        if(isset($_REQUEST['test'])) {
            $selectVisit = Visits::findOne(11509383);
        }

        if ($selectVisit) {
            if ($selectVisit->cloaking == 0) {
                $appInfo = Apps::find()->where(['id' => $selectVisit->devices->app_id])->one();

                $logger->data['app']['package'] = $appInfo['package'];

                $trafficRoute = $appInfo['traffic_route'];
                $appIdOriginal = $selectVisit->devices->app_id;
                $traffic_route_app_id = \Yii::$app->params['traffic_route_app_id'];

                if ($trafficRoute == 0) {
                    $appId = $appIdOriginal;
                } else {
                    $appId = $traffic_route_app_id;
                }

                if($appId !== $traffic_route_app_id && $trafficRoute != 1) {
                    $countryInfo = ApiHelper::getCountryInfo($selectVisit, $appId);

                    $linksInfo = Links::find()
                        ->where(['linkcountry_id' => $countryInfo['id']])
                        ->andWhere(['is_main' => 1])
                        ->andWhere(['archived' => 0])
                        ->one();

                    $trafficRoute = BalanceController::changeBalance($linksInfo['user_id'], $appId, $countryInfo['country_code']);

                    if ($trafficRoute == 1) {
                        $appId = $traffic_route_app_id;
                    }
                }

                //$responseServer->setUrl(json_decode($selectVisit->server_response, true)[$responseServer->urlName]);

                $dataCampAf = (array)$dataJson;
                if (isset($dataCampAf['campaign_af:']) || isset($dataJson->campaign_af)) {
                    if(isset($dataCampAf['campaign_af:']))
                        $campaign_af = $dataCampAf['campaign_af:'];
                    else
                        $campaign_af = $dataJson->campaign_af;

                    $separatorList = ["~", "/", "|", ";", "_"];
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
                    $deeplink = ApiHelper::parseDeepLinks($deeplink);
                }

                // if(isset($_REQUEST['test'])) {
                //     var_dump($deeplink);
                //     var_dump($dataJson->deeplink);
                //     var_dump($dataJson->campaign_af);
                // }
                //если есть диплинка то парсим её в массив
                if (isset($dataJson->deeplink) && strlen($dataJson->deeplink) > 0) {
                    $deeplink = ApiHelper::parseDeepLinks($dataJson->deeplink);
                }
                
                if (isset($dataJson->deeplink) && isset($dataJson->deeplink_branch) && strlen($dataJson->deeplink) <= 0 && strlen($dataJson->deeplink_branch) > 0) {
                    $deeplink = ApiHelper::parseDeepLinks($dataJson->deeplink_branch);
                }
                
                // if (isset($dataJson->deeplink) && isset($dataJson->campaign_af) && strlen($dataJson->deeplink) <= 0 && strlen($dataJson->campaign_af) > 0) {
                //     $deeplink = ApiHelper::parseDeepLinks($dataJson->campaign_af);
                // }
                // if(isset($_REQUEST['test'])) {
                //     var_dump($deeplink); die;
                // }
                if (isset($dataJson->deeplink) && isset($dataJson->deeplink_fb) && strlen($dataJson->deeplink) <= 0 && strlen($dataJson->deeplink_fb) > 0) {
                    $deeplink = ApiHelper::parseDeepLinks($dataJson->deeplink_fb);
                }

		if (isset($dataJson->deeplink_fb) && strlen($dataJson->deeplink_fb) > 0) {
		    $deeplink = ApiHelper::parseDeepLinks($dataJson->deeplink_fb);
		}
	
                $countryInfo = ApiHelper::getCountryInfo($selectVisit, $appId);
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
                } else {
                    $campaign_af = null;
                    $jsonData['campaign_name'] = $campaign_af;
                }
		
                $newUrlOption = ApiHelper::replaceVarUrl($deeplink ?? false, $countryInfo, $jsonData);
			
                $responseServer->setLinkId($newUrlOption['link_data']['id']);

                $redirectHash = md5($selectVisit->access_token);
                //$responseServer->setUrl($newUrlOption['url']);
                //$newUrl = 'https://'.$_SERVER['HTTP_HOST'].'/api/open-url?visitid='.$selectVisit->id.'&hash='.$redirectHash;
                //$responseServer->setUrl($newUrl);
                if (ApiHelper::validateUrl($linkInfo->url)) {
                    $newUrl = 'https://'.$_SERVER['HTTP_HOST'].'/api/open-url?visitid='.$selectVisit->id.'&hash='.$redirectHash;
                } else {
                    $newUrl = $linkInfo->url;
                }
		
                $responseServer->setUrl($newUrl);
                $params = ApiHelper::getParams($appIdOriginal, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id'], 1);
                switch ($trafficRoute) {
                    case 1:
                        $params = array_merge(
                            $params,
                            ApiHelper::getParams($appIdOriginal, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id'])
                        );

                        $params = array_merge(
                            $params,
                            ApiHelper::getParams($appId, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id'])
                        );

                        break;
                    default:
                        $params = array_merge(
                            $params,
                            ApiHelper::getParams($appId, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id'])
                        );
                        break;
                }
                $params['pid'] = $newUrlOption['pid'];

                $responseServer->setExtra($params);

//                    $serverResponse = json_decode($selectVisit->server_response);
//                    $serverResponse[1] = $responseServer->getResponse();
//
//                    $extraResponse = json_decode($selectVisit->extra);
//                    $extraResponse[1] = $dataJson;

                $redirect_info = ['hash' => $redirectHash];
                $selectVisit->redirect_data = json_encode($redirect_info);
                $selectVisit->is_redirect = 0;

                $selectVisit->link_id = $responseServer->getLinkId();
                $selectVisit->access_token = null;
                $selectVisit->url = $newUrlOption['url'];
                
                $selectVisit->campaign_af = $campaign_af;

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
    }

    public function doubleAuth($deviceData, $responseServer, $logger = -1)
    {
        //заносим повторный запуск в БД
        $package = $deviceData->package;
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        $visit = Visits::getDb()->cache(function () use ($deviceData) {
            return Visits::find()->where(['device_id' => $deviceData->id])->one();
        }, 60); //кэш на 1 мин

        $newVisit = new Visits();
        foreach ($visit as $key => $value) {
            if ($key != "id" && $key != "date" && $key != "is_first" && $key != "is_redirect") {
                $newVisit[$key] = $value;
            }
        }
        $newVisit->save();
        $logJSON['request'] = (array)$deviceData;
        $responseServer->setUrl($visit->url ?? false);

        $logJSON['response'] = $responseServer->getResponse();

        $logger->data['user']['visit_id'] = $newVisit->id;
        $logger->data['device'] = $logJSON;
        $logger->infoSend("DeviceDoubleAuth");
//            $cache->set($cacheKeyAuth, $responseServer->getResponse(), 10);
        print json_encode($responseServer->getResponse());
        exit();
    }

    public function actionOpenUrl()
    {
        if(!isset($_GET['visitid']) || !isset($_GET['hash'])) exit;

        $ip = ApiHelper::getIP();
        $visit = Visits::findOne($_GET['visitid']);
        if(!isset($visit)) exit;

        $redirect_info = ApiHelper::isJSON($visit->redirect_data) ? json_decode($visit->redirect_data, true) : [];

        if(!isset($redirect_info['hash']) || empty($redirect_info) || $redirect_info['hash'] != $_GET['hash']) {
            header('Location: //block.com');
            exit();
        }
	

        if($visit->filterlog->ip != $ip) {
            header('Location: //block.com');
            $this->saveRedirectInfo($visit, $visit->filterlog->ip, $ip, 'block');
        } else {
            header('Location: '.$visit->url);
            $this->saveRedirectInfo($visit, $ip, $ip, $visit->url);
        }

        exit();
    }

    public function saveRedirectInfo($visit, $ipOriginal, $ipFactual, $url)
    {
        $redirect_info = json_decode($visit->redirect_data, true);

        unset($redirect_info['hash']);
        $visit->is_redirect = 1;
        $redirect_info['ip_original'] = $ipOriginal;
        $redirect_info['ip_factual'] = $ipFactual;
        $redirect_info['url'] = $url;

        $visit->redirect_data = json_encode($redirect_info);

        $appInfo = Apps::find()->where(['id' => $visit->devices->app_id])->one();

        $redirect_info['is_open'] = true;
        $logger = new LogsController();
        $logger->data['data'] = $redirect_info;
        $logger->data['package'] = $appInfo->package ?? '';
        $logger->data['geo'] = $visit->link->linkcountry->country_code;
        $logger->infoSend("DeviceOpenUrl");

        $visit->save();
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

        if(ApiHelper::isJSON($content)) {
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
