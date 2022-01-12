<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_params}}".
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $linkcountry_id
 * @property string $key
 * @property string|null $value
 * @property string|null $created
 * @property int|null $access_level
 * @property int|null $archived
 * @property int $is_for_bot
 */
class Params extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_params}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'user_id', 'linkcountry_id', 'key', 'is_for_bot'], 'required'],
            [['app_id', 'user_id', 'linkcountry_id', 'access_level', 'archived', 'is_for_bot'], 'integer'],
            [['created'], 'safe'],
            [['key'], 'string', 'max' => 255],
            [['value'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'app_id' => Yii::t('app', 'App ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'linkcountry_id' => Yii::t('app', 'Linkcountry ID'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'created' => Yii::t('app', 'Created'),
            'access_level' => Yii::t('app', 'Access Level'),
            'archived' => Yii::t('app', 'Archived'),
            'is_for_bot' => 'Для бота',
        ];
    }

    public function getCountries()
    {
        return $this->hasOne(Linkcountries::class, ['id' => 'linkcountry_id']);
    }

    public function getApp()
    {
        return $this->hasOne(Apps::class, ['id' => 'app_id']);
    }
}
