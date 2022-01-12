<?php

namespace app\modules\AppsApi\interfaces;

interface UrlStrategy
{
    public function auth();
    public function getUrl($visit);
}