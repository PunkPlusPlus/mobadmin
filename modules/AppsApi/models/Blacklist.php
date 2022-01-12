<?php

namespace app\modules\AppsApi\models;

class Blacklist extends \app\models\Blacklist
{
    public static function getItem($idfa)
    {
        return Blacklist::find()->where(['idfa' => $idfa])->one() ?? false;
    }

    public static function getBannedItem($idfa)
    {
        return Blacklist::find()->where(['idfa' => $idfa])->andWhere(['block' => true])->one() ?? false;
    }
}