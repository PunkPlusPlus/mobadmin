<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_visits}}".
 *
 * @property int $id
 * @property int|null $linkcountry_id
 * @property int $device_id
 * @property string $user_ip
 * @property string $country_code
 * @property string $isp
 * @property int $proxy
 * @property string $ip_data
 * @property string|null $extra
 * @property int $cloaking
 * @property string $binom_response
 * @property string $server_response
 * @property string $date
 * @property string $click_id
 * @property string $payout
 *
 * @property Linkcountries $linkcountry
 */
class Visits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_visits}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id'], 'required'],
            [['link_id', 'device_id', 'filterlog_id'], 'integer'],
            [['server_response'], 'string'],
            [['access_token'], 'string'],
            [['appsflyer_device_id'], 'string'],
            [['url', 'onesignal_id'], 'string'],
            [['deeplink'], 'string'],
            [['server_name'], 'string'],
            [['date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'link_id' =>  Yii::t('app', 'link_id'),
            'device_id' => Yii::t('app', 'device_id'),
            'extra' => Yii::t('app', 'params'),
            'server_response' => Yii::t('app', 'server_response'),
            'filterlog_id' => Yii::t('app', 'filterlog_id'),
            'date' => Yii::t('app', 'date'),
            'access_token' => Yii::t('app', 'access_token'),
            'server_name' => Yii::t('app', 'server_name'),
            'appsflyer_device_id' => Yii::t('app', 'appsflyer_device_id'),
        ];
    }
	
    public function getDevices()
    {
        return $this->hasOne(Devices::className(), ['id' => 'device_id']);
    }

    public function getFilterlog()
    {
        return $this->hasOne(Log::className(), ['id' => 'filterlog_id']);
    }
	
    public function getLink()
    {
        return $this->hasOne(Links::className(), ['id' => 'link_id']);
    }
}
