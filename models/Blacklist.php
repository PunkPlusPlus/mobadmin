<?php


namespace app\models;

use yii\db\ActiveRecord;


class Blacklist extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_black_list}}';
    }

    public function rules()
    {
        return [
            [['idfa'], 'string', 'max' => 255],
            [['block'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idfa' => 'IDFA',
            'block' => 'BLOCK'
        ];
    }

    public static function getBannedVisits()
    {
        $banned_visits = Blacklist::find()
            ->where(['block' => true])
            ->all();
        return $banned_visits;
    }

}
