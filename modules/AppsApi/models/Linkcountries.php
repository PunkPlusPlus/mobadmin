<?php

namespace app\modules\AppsApi\models;

class Linkcountries extends \app\models\Linkcountries
{
    public static function findActiveCountries($appInfo)
    {
        $allCountryApp = \app\models\Linkcountries::find()
            ->where(['app_id' => $appInfo['id']])
            ->andWhere(['archived' => 0])
            ->all();
        return $allCountryApp;
    }

    public static function getNamingLinkcountry($appInfo)
    {
        $country = Linkcountries::find()
            ->where(['app_id' => $appInfo->id])
            ->andWhere(['country_code' => 'none'])
            ->andWhere(['archived' => 0])
            ->one();
        return $country ?? false;
    }
}