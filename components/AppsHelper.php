<?php

namespace app\components;

use app\models\Apps;
use app\models\Linkcountries;
use app\models\Links;


class AppsHelper
{
    public static function deleteAll($data)
    {
        $linkcountries = self::getLinkcountry($data);
        if (!$linkcountries) return false;
        foreach ($linkcountries as $country) {
            $links = Links::find()
                ->where(['linkcountry_id' => $country->id])
                ->andWhere(['archived' => 0])->all();
            if ($links) {
                foreach ($links as $link) {
                    $link->archived = 1;
                    $link->save();
                }
            }
            $country->delete();
        }
    }

    private static function getLinkcountry($data)
    {
        $linkcountries = array();
        $geo = $data['geo'];
        $app = Apps::find()->where(['package' => $data['package']])->one();
        foreach ($geo as $country) {
            $linkcountry = Linkcountries::find()
                ->where(['country_code' => $country])
                ->andWhere(['app_id' => $app->id])
                ->andWhere(['archived' => 0])->one();
            if ($linkcountry == null) {
                $linkcountry = new Linkcountries();
                $linkcountry->app_id = $app->id;
                $linkcountry->country_code = $country;
                $linkcountry->archived = 0;
                if (!$linkcountry->save()) return false;
            }
            array_push($linkcountries, $linkcountry);
        }
        return $linkcountries;
    }

    public static function createLink($data, $user, $isMain = 0)
    {
        $errors = array();
        $linkcountries = self::getLinkcountry($data);
        if (!$linkcountries) return false;
        foreach ($linkcountries as $linkcountry) {
            //$linkcountry->blockOrganic($user);
            //$link = self::checkNaming($data, $linkcountry->country_code, $isMain);
            //if (!$link) $link = new Links();
            $link = new Links();
            $link->key = 'camp_name';
            $link->linkcountry_id = $linkcountry->id;
            $link->user_id = $user->id;
            $link->label = $link->value;
            $link->url = $data['url'];
            $link->archived = 0;
            $link->is_main = $isMain;
            if ($isMain) {
                $link->value = "Organic";
            } else {
                $link->value = $data['value'];
            }
            if (!$link->save()) {
                array_push($errors, $linkcountry->country_code);
            }
            //if (!$link->save()) return ResponseComponent::getJson('error', 'Internal error');
        }
        //$naming = self::getCampName($link);
        //$message['naming'] = $naming;
        if (count($errors) > 0) {
            if (count($errors) == count($linkcountries)) {
                return false;
            }
            $message['unavailable_geos'] = $errors;
        }
        return true;
    }

}
