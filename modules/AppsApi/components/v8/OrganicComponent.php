<?php

namespace app\modules\AppsApi\components\v8;

use \app\modules\AppsApi\models\Linkcountries;
use app\models\Links;
use app\modules\AppsApi\interfaces\UrlStrategy;
use app\modules\AppsApi\traits\LoggingTrait;

class OrganicComponent extends BaseUrlComponent implements UrlStrategy
{
    use LoggingTrait;

    protected $log = array();
    protected $request;
    protected $cloakingInfo;
    protected $deviceInfo = ['deviceModel' => null, 'deviceName' => null, 'deviceBrand' => null, 'resolution' => null];
    protected $access_token;
    protected $appInfo;
    protected $separator;
    protected $deeplink = null;

    public function auth()
    {
        $this->log['method'] = 'Organic';
        $this->log['app']['package'] = $this->appInfo->package;
        $this->log['request'] = (array)$this->request;

        $allowedCountry = array();
        $allCountryApp = Linkcountries::findActiveCountries($this->appInfo);

        foreach ($allCountryApp as $appCountry)
            array_push($allowedCountry, $appCountry->country_code);

        $blockByList = ApiHelper::checkBlackList($this->appInfo, $this->request->idfa ?? null);
        $this->cloakingInfo = ApiHelper::cloaking($this->deviceInfo, $allowedCountry, $blockByList, $this->request->idfa ?? null);

        $this->log['cloaking']['country_code'] = $this->cloakingInfo['countryCode'] ?? null;
        $this->log['cloaking']['is_bot'] = $this->cloakingInfo['isBot'];
        $this->log['cloaking_test'] = $this->cloakingInfo;

        $isBot = $this->cloakingInfo['isBot'];
        $countryInfo = ApiHelper::getCountryInfo($allCountryApp, $this->cloakingInfo['countryCode'] ?? null);

        $this->log['link_country']['id'] = $countryInfo->id;
        $this->log['link_country']['countryCode'] = $countryInfo->country_code;

        $linksInfo = Links::getMainLink($countryInfo);
        $device = ApiHelper::deviceCheck($isBot, $linksInfo, $this->request, RequestComponent::isDoubleAuth());
        $this->access_token = ApiHelper::generateToken($device);

        if (!$linksInfo) {
            $isBot = true;
            ResponseConfig::getInstance()->setUrl(false);
        }

        ResponseConfig::getInstance()->setDeviceId($device['id']);
        ResponseConfig::getInstance()->setExtra(ApiHelper::botCheck($isBot, $countryInfo, $this->appInfo));

        $visit = $this->newVisit($device, $linksInfo);

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

        $allCountryApp = Linkcountries::findActiveCountries($this->appInfo);
        $country = ApiHelper::getCountryInfo($allCountryApp, $this->cloakingInfo['countryCode'] ?? null);
        $linksInfo = Links::getMainLink($country);

        if ($linksInfo == null) {
            $this->log['errors'] = "link not found";
            self::sendLog($this->log, "DeviceAuth");
            return json_encode(ResponseConfig::getInstance()->getErrorResponse());
        }

        $this->log['link_country']['id'] = $country->id;
        $this->log['link_country']['country_code'] = $country->country_code;
        $this->log['link']['id'] = $linksInfo->id;

        $newUrlOption = ApiHelper::replaceVarUrl(false, $country, (array)$this->request);
        $redirectHash = md5($visit->access_token);
        $newUrl = $linksInfo->url;

        if (ApiHelper::validateUrl($linksInfo->url)) $newUrl = ApiHelper::getTrackUrl($visit, $redirectHash);

        $params = ApiHelper::getParams($this->appInfo->id, $newUrlOption['link_data']['user_id'], $newUrlOption['link_data']['linkcountry_id'], 1);
        $params['visitId'] = $visit->id;
        unset($params['code']);
        $redirect_info = ['hash' => $redirectHash];

        ResponseConfig::getInstance()->setLinkId($newUrlOption['link_data']['id']);
        ResponseConfig::getInstance()->setUrl($newUrl);
        //ResponseConfig::getInstance()->setExtra($params);

        $this->saveVisit($redirect_info, $newUrlOption, $visit);
        $this->saveDevice($visit->device_id);

        $this->log['response'] = (array)ResponseConfig::getInstance()->getResponse();

        self::sendLog($this->log, "DeviceAuth");

        return json_encode(ResponseConfig::getInstance()->getResponse());
    }
}
