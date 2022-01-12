<?php

namespace app\models;

use yii\base\Model;
use yii\db\ActiveRecord;

class Tokens extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_tokens}}';
    }

    public function rules()
    {
        return [
            [['id'], 'number'],
            [['token'], 'string'],
            [['user_id'], 'number']
        ];
    }
}
