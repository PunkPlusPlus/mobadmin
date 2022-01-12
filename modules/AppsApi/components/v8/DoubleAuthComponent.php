<?php

namespace app\modules\AppsApi\components\v8;

use app\models\Apps;
use app\models\Visits;
use app\modules\AppsApi\interfaces\UrlStrategy;
use app\modules\AppsApi\traits\LoggingTrait;

class DoubleAuthComponent extends BaseUrlComponent implements UrlStrategy
{
    use LoggingTrait;

    protected $log = array();
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        $this->appInfo = Apps::getApp($this->request->package);
        $this->separator = ApiHelper::detectSeparator($this->request);
    }

    public function auth()
    {
        $this->log['method'] = "DeviceDoubleAuth";
        $this->log['request'] = (array)$this->request;

        $data = $this->request;
        $package = $data->package;

        $visit = Visits::getDb()->cache(function () use ($data) {
            return Visits::find()->where(['device_id' => $data->id])->one();
        }, 60); //кэш на 1 мин

        $newVisit = new Visits();

        if (!$visit) return null;

        foreach ($visit as $key => $value) {
            if ($key != "id" && $key != "date" && $key != "is_first" && $key != "is_redirect") {
                $newVisit->$key = $value;
            }
        }

        $this->log['user']['visit_id'] = $newVisit->id;
        $this->log['package'] = $package;

        if ($newVisit->save()) return $newVisit;
        return null;
    }

    public function getUrl($visit)
    {
        if (!$visit) {
            $this->log['errors'] = "visit not found";
            self::sendLog($this->log, "DeviceAuth");
            return json_encode(ResponseConfig::getInstance()->getErrorResponse());
        }

        $url = $visit->url ?? false;

        ResponseConfig::getInstance()->setUrl($url);
        ResponseConfig::getInstance()->setExtra(json_decode($visit->extra, true) ?? "{}");
        $response = ResponseConfig::getInstance()->getResponse();

        $this->log['response'] = (array)$response;

        self::sendLog($this->log, "DeviceDoubleAuth");

        return $response;
    }
}