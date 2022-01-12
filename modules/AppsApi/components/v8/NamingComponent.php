<?php

namespace app\modules\AppsApi\components\v8;

use app\controllers\LogsController;
use app\models\Links;
use app\models\Namings;
use app\modules\AppsApi\interfaces\UrlStrategy;
use app\modules\AppsApi\traits\LoggingTrait;

class NamingComponent extends BaseUrlComponent implements UrlStrategy
{
    use LoggingTrait;

    protected $log = array();
    protected $request;
    protected $cloakingInfo;
    protected $deviceInfo = ['deviceModel' => null, 'deviceName' => null, 'deviceBrand' => null, 'resolution' => null];
    protected $access_token;
    protected $appInfo;
    protected $separator;

    public function auth()
    {
        $this->log['method'] = 'Naming';
        $this->log['app']['package'] = $this->appInfo->package;
        $this->log['request'] = (array)$this->request;

        $link = $this->getNamingLink($this->appInfo);
        $blockByList = ApiHelper::checkBlackList($this->appInfo, $this->request->idfa ?? null);
        $this->cloakingInfo = ApiHelper::cloaking($this->deviceInfo, null, $blockByList, false);

        $this->log['cloaking']['country_code'] = $this->cloakingInfo['countryCode'] ?? null;
        $this->log['cloaking']['is_bot'] = $this->cloakingInfo['isBot'];

        if (!$link) {
            $this->log['errors'] = "link not found";
            self::sendLog($this->log, "DeviceAuth");
            return json_encode(ResponseConfig::getInstance()->getErrorResponse());
        }

        $device = ApiHelper::deviceCheck($this->cloakingInfo['isBot'], $link, $this->request, RequestComponent::isDoubleAuth());

        $this->log['link_country']['id'] = "Naming";
        $this->log['link_country']['country_code'] = "Naming";
        $this->log['link']['id'] = $link->id;

        $this->access_token = ApiHelper::generateToken($device);

        //TODO new extra mechanism
        ResponseConfig::getInstance()->setExtra(ApiHelper::botCheckNaming($this->cloakingInfo['isBot'], $this->appInfo));
        ResponseConfig::getInstance()->setDeviceId($device->id);

        $visit = $this->newVisit($device, $link);

        $this->log['user']['visit_id'] = $visit->id;

        return $visit;
    }

    public function getUrl($visit)
    {
        if (!$visit || $visit->cloaking) {
            $this->log['errors'] = "is bot";
            self::sendLog($this->log, "DeviceAuth");
            return json_encode(ResponseConfig::getInstance()->getErrorResponse());
        }

        $link = $this->getNamingLink();

        if ($link == null) {
            $this->log['errors'] = "link not found";
            self::sendLog($this->log, "DeviceAuth");
            return json_encode(ResponseConfig::getInstance()->getErrorResponse());
        }

        $this->log['link_country']['id'] = "Naming";
        $this->log['link_country']['country_code'] = "Naming";
        $this->log['link']['id'] = $link->id;

        $deeplink = $this->getDeeplink($this->request->campaign_af ?? null);
        $newUrlOption = ApiHelper::replaceNamingUrl($deeplink, $link, (array)$this->request);
        $redirectHash = md5($visit->access_token);
        $newUrl = $link->url;

        if (ApiHelper::validateUrl($link->url)) $newUrl = ApiHelper::getTrackUrl($visit, $redirectHash);

        $params = ApiHelper::getNamingParams($this->appInfo->id, $newUrlOption['link_data']['user_id'], 1);
        $params['visitId'] = $visit->id;
        unset($params['code']);
        $redirect_info = ['hash' => $redirectHash];

        ResponseConfig::getInstance()->setLinkId($newUrlOption['link_data']['id']);
        ResponseConfig::getInstance()->setUrl($newUrl);

        $this->saveVisit($redirect_info, $newUrlOption, $visit);
        $this->saveDevice($visit->device_id);
        //ResponseConfig::getInstance()->setExtra($params);

        $this->log['response'] = (array)ResponseConfig::getInstance()->getResponse();

        self::sendLog($this->log, "DeviceAuth");

        return (array)ResponseConfig::getInstance()->getResponse();
    }

    private function getNamingLink()
    {
        $namings = Namings::find()
            ->where(['app_id' => $this->appInfo->id])
            ->andWhere(['archived' => 0])
            ->one();
        if (!$namings) return false;
        $link = Links::find()
            ->where(['linkcountry_id' => -1])
            ->andWhere(['id' => $namings->link_id])
            ->one();
        return $link ?? false;
    }

    private function getDeeplink($campaign_af)
    {
        if (isset($this->separator) && $this->separator != null) {
            $subList = explode($this->separator, $campaign_af);
            $deeplink = "app://camp_name=";
            for ($i = 0; $i < count($subList); $i++) {
                if ($i == 0) {
                    $deeplink .= $subList[$i];
                } else {
                    $deeplink .= "&sub_" . $i . "=" . $subList[$i];
                }
            }
        } else {
            $deeplink = "app://camp_name=" . $campaign_af;
        }

        $deeplink = \app\basic\ApiHelper::parseDeepLinks($deeplink);
        //если есть диплинка то парсим её в массив
        if (isset($this->request->deeplink) && strlen($this->request->deeplink) > 0) {
            $deeplink = \app\basic\ApiHelper::parseDeepLinks($this->request->deeplink);
        }
        if (isset($this->request->deeplink) && isset($this->request->deeplink_branch) && strlen($this->request->deeplink) <= 0 && strlen($this->request->deeplink_branch) > 0) {
            $deeplink = ApiHelper::parseDeepLinks($this->request->deeplink_branch);
        }
        if (isset($this->request->deeplink) && isset($this->request->deeplink_fb) && strlen($this->request->deeplink) <= 0 && strlen($this->request->deeplink_fb) > 0) {
            $deeplink = ApiHelper::parseDeepLinks($this->request->deeplink_fb);
        }

        return $deeplink;
    }
}

