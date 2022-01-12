<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_notifications}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $app_id
 * @property int $source_id
 * @property string $source_option
 * @property string|null $source_key1
 * @property string|null $source_key2
 * @property string|null $source_key3
 * @property string|null $source_key4
 */
class Notifications extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_notifications}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'source_id', 'source_key1'], 'required', 'message' => 'Необходимо заполнить поле'],
            [['user_id', 'app_id', 'source_id'], 'integer'],
            [['source_key1', 'source_key2', 'source_key3', 'source_key4', 'comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'app_id' => Yii::t('app', 'App ID'),
            'source_id' => Yii::t('app', 'Source ID'),
            'source_key1' => Yii::t('app', 'Source Key1'),
            'source_key2' => Yii::t('app', 'Source Key2'),
            'source_key3' => Yii::t('app', 'Source Key3'),
            'source_key4' => Yii::t('app', 'Source Key4'),
            'comment' => Yii::t('app', 'comment')
        ];
    }

    public function getApp()
    {
        return $this->hasOne(Apps::class, ['id' => 'app_id']);
    }
}
