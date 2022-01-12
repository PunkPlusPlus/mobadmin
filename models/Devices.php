<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_devices}}".
 *
 * @property int $id
 * @property string $uid
 * @property string|null $device_model
 * @property string|null $device_name
 * @property string|null $device_vendor
 * @property string|null $resolution
 * @property string|null $language
 * @property string|null $package
 * @property string|null $extra
 * @property string $date_reg
 */
class Devices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_devices}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_reg'], 'safe'],
            [['uid', 'model', 'name', 'brand', 'resolution', 'idfa', 'appsflyer_id'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10],
            [['app_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'UID',
            'model' => Yii::t('app', 'model'),
            'name' => Yii::t('app', 'name'),
            'brand' => Yii::t('app', 'brand'),
            'resolution' => Yii::t('app', 'resolution'),
            'language' => Yii::t('app', 'device_language'),
            'date_reg' => Yii::t('app', 'date_reg'),
            'app_id' => Yii::t('app', 'app_id'),
        ];
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(Apps::className(), ['id' => 'app_id']);
    }
}
