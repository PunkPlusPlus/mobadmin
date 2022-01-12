<?php

namespace app\modules\AppsApi\components\v8;

use app\modules\AppsApi\interfaces\UrlStrategy;

class UrlContext
{
    private $strategy;

    public function __construct(UrlStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(UrlStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function execute()
    {
        $visit = $this->strategy->auth();
        if (is_string($visit)) $visit = null;
        $response = $this->strategy->getUrl($visit);
        return $response;
    }

    public function auth()
    {
        $visit = $this->strategy->auth();
        return $visit;
    }

    public function getUrl($visit)
    {
        $response = $this->strategy->getUrl($visit);
        return $response;
    }

}