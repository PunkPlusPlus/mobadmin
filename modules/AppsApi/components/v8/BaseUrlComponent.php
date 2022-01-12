<?php

namespace app\modules\AppsApi\components\v8;

use app\models\Apps;
use app\models\Devices;
use app\models\Visits;

class BaseUrlComponent
{
    protected $campaign_af;

    public function __construct($request)
    {
        $this->request = $request;
        $this->appInfo = Apps::getApp($this->request->package);
        $this->separator = ApiHelper::detectSeparator($this->request);
    }

    protected function newVisit($device, $link)
    {
        $visits = new Visits();
        $visits->device_id = $device->id;

        if (count(ResponseConfig::getInstance()->getExtra()) > 0) {
            $visits->extra = json_encode(ResponseConfig::getInstance()->getExtra());
        } else {
            ResponseConfig::getInstance()->setExtra('{}');
        }

        $visits->cloaking = $this->cloakingInfo['isBot'] ?? true ? 1 : 0;
        $visits->filterlog_id = $this->cloakingInfo['logId'];
        $visits->link_id = $link->id;
        $visits->access_token = $this->access_token;
        $visits->is_first = ApiHelper::isFirst($this->request) ? 1 : 0;
        if (strlen($device->link_id) > 0) {
            $visits->link_id = $device->link_id;
        }
        if (isset($_SERVER['SERVER_NAME'])) $visits->server_name = $_SERVER['SERVER_NAME'];
        $visits->save();
        return $visits;
    }

    /**
     * Mutable function
     * @param $redirect_info
     * @param $newUrlOption
     * @param $visits
     */
    protected function saveVisit($redirect_info, $newUrlOption, &$visits)
    {
        $visits->redirect_data = json_encode($redirect_info);
        $visits->is_redirect = 0;
        $visits->link_id = ResponseConfig::getInstance()->getLinkId();
        $visits->url = $newUrlOption['url'];
        $visits->campaign_af = $this->request->campaign_af ?? null;

        if (isset($this->request->deeplink) && strlen($this->request->deeplink) > 0) {
            $visits->deeplink = $this->request->deeplink;
        }

        $visits->save();

        $this->log['device']['id'] = $visits->device_id;
        $this->log['device']['visit_id'] = $visits->id;
    }

    protected function saveDevice($device_id)
    {
        $device = Devices::find()
            ->where(['id' => $device_id])
            ->one();
        $device->link_id = ResponseConfig::getInstance()->getLinkId();
        $device->app_id = $this->appInfo->id;
        $device->save();
    }

    // public function execute()
    // {
    //     $visit = $this->auth();
    //     if (is_string($visit)) $visit = null;
    //     $response = $this->getUrl($visit);
    //     return $response;
    // }
}