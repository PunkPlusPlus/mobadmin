<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_settings}}".
 *
 * @property int $id
 * @property string $offer_list
 */
class Queue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_queue}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'count'], 'required'],
            [['app_id', 'count'], 'number'],
        ];
    }

    public static function addItem($id)
    {

    }

    public static function getItems()
    {
        $items = Queue::find()
            ->where(['OR', ['count' => 0], ['count' => 1], ['count' => 2]])
            ->all();
        if (empty($items) || $items == null) {
            return false;
        } else {
            return $items;
        }
    }

    /**
     * {@inheritdoc}
     */
//    public function attributeLabels()
//    {
//        return [
//            'id' => 'ID',
//            'key' => 'Ключ',
//            'value' => 'Значение',
//        ];
//    }
}

