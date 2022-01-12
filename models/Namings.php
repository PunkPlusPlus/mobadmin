<?php

namespace app\models;

use yii\db\ActiveRecord;

class Namings extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_namings}}';
    }

    public function rules()
    {
        return [
            [['app_id', 'link_id'], 'integer'],
            [['archived'], 'boolean'],
        ];
    }

    public static function createNamingLink($user, $url, $value, $app)
    {
        $naming = self::find()->where(['app_id' => $app->id])->one();
        if ($naming) {
            $link = Links::findOne($naming->link_id);
        } else {
            $naming = new Namings();
            $link = new Links();
        }

        $link->key = 'camp_name';
        $link->linkcountry_id = -1;
        $link->user_id = $user->id;
        $link->url = $url;
        $link->archived = 0;
        $link->is_main = 1;
        $link->value = "Naming";
        if (!$link->save()) return false;

        //$naming = new Namings();
        $naming->app_id = $app->id;
        $naming->link_id = $link->id;
        $naming->archived = 0;
        if (!$naming->save()) return false;
        return true;
    }
}