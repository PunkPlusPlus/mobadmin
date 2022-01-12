<?php

namespace app\modules\AppsApi\components\v8;

class UrlFactory
{
    public static function getUrlStrategy($request)
    {
        if (RequestComponent::isDoubleAuth()) {
            return new DoubleAuthComponent($request);
        }
        if (self::isOrganic($request)) {
            return new OrganicComponent($request);
        } else {
            return new NamingComponent($request);
        }
    }

    private static function isOrganic($request): bool
    {
        $separator = ApiHelper::detectSeparator($request);
        if ($separator) return false;
        if (isset($request->campaign_af) && strlen($request->campaign_af) > 0) return false;
        if (isset($request->deeplink) && strlen($request->deeplink) > 0) return false;
        return true;
    }
}