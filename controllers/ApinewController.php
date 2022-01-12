<?php


namespace app\controllers;



use app\basic\ApiHelper;
use app\components\ApiComponent;
use app\models\Apps;
use app\models\Devices;
use app\models\Links;
use app\models\Visits;
use app\modules\api\ApiModule;
use Yii;

class ApinewController extends ApiController
{
    public $enableCsrfValidation = false;
    public $freeAccessActions = ['auth'];

    public function actionOption()
    {
        $logger = new LogsController();

        $data = $_POST['data'] ?? $_GET['data'] ?? false;
        $validData = ApiHelper::validateRequest($data);
        $logger->data['message']['valid_data'] = $validData;
        if ($validData) {
            $selectVisit = Visits::find()->where(['id' => $validData['visitId']])->one();
            $selectVisit->onesignal_id = $validData['onesignal_id'];
            $pid = $selectVisit->link->user_id;
            $tags = array(
                'pid' => $pid,
                'moder' => boolval($selectVisit->filterlog->is_bot) ?? false
            );
            $logger->data['message']['pid'] = $pid;
            if ($selectVisit->save()) {
                // sending pid to OneSignal
                $user_id = $selectVisit->onesignal_id;
                $app_id = ApiHelper::getAppId($selectVisit);
                if ($app_id && $user_id) {
                    $response = ApiHelper::sendOnesignal($tags, $user_id, $app_id);
                    $logger->data['message']['pid_sending'] = $response;
                } else {
                    $logger->data['message']['pid_sending'] = "Data error";
                }
                // end sending
                $logger->data['message']['response'] = json_encode(['success' => true]);
                $logger->infoSend("Option");
                return json_encode(['success' => true]);
            } else {
                $logger->data['message']['response'] = json_encode(['success' => false]);
                $logger->infoSend("Option");
                return json_encode(['success' => false]);
            }
        } else {
            $logger->data['message']['response'] = json_encode(['success' => false, 'error' => "Invalid json"]);
            $logger->infoSend("Option");
            return json_encode(['success' => false,'error' => "Invalid json"]);
        }
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
		
		if (isset($dataJson->conversion_data)) {
                    try {
                        $campaign = $dataJson->conversion_data->data->campaign;
                    } catch (\Exception $e) {

                    }
                }

                //$responseServer->setUrl(json_decode($selectVisit->server_response, true)[$responseServer->urlName]);

                $dataCampAf = (array)$dataJson;
                if (isset($dataCampAf['campaign_af:']) || isset($dataJson->campaign_af)) {
                    if(isset($dataCampAf['campaign_af:']))
                        $campaign_af = $dataCampAf['campaign_af:'];
                    else
                        $campaign_af = $dataJson->campaign_af;
		    
		    try {
                        $campaign_af = $campaign;
                    } catch(\Exception $e) {

                    }

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
                $selectVisit->access_token = Yii::$app->getSecurity()->generateRandomString();
                $selectVisit->url = $newUrlOption['url'];
		$responseServer->setAccessToken($selectVisit->access_token);
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

}
