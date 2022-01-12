<?php

namespace app\modules\AppsApi\components\v8;

class ResponseConfig
{
    public $urlName = "bonus";
    public $appIdName = "appid";
    public $deviceIdName = "id";
    public $extraName = "extra";
    public $errorName = "error";
    public $accessTokenName = "access_token";
    public $linkId = -1;
    public $logMessage = array();

    private $response;
    private static $instance;

    protected function __construct()
    {
        $this->response = [
            $this->urlName => false,
            $this->extraName => []
        ];
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
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

    public function getErrorResponse()
    {
        $this->response[$this->urlName] = false;
        //$this->response[$this->extraName] = "{}";
        return $this->response;
    }
}