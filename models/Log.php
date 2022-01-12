<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tbl_log".
 *
 * @property int $id
 * @property string $at_datetime
 * @property string|null $ip
 * @property string|null $ipv6
 * @property string|null $ua
 * @property string|null $referer
 * @property string|null $referer_prelanding
 * @property string|null $manager_key
 * @property string|null $language
 * @property string|null $country
 * @property string|null $city
 * @property string|null $isp
 * @property string|null $asn
 * @property string|null $os
 * @property string|null $browser
 * @property string|null $external_uclick
 * @property string|null  $log_type
 * @property string|null $detailed
 * @property int|null $is_bot
 * @property string|null $meta_data
 */
class Log extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_filterlogs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_bot'], 'integer'],
            [['at_datetime'], 'safe'],
            [['log_type', 'meta_data' ], 'string'],
            [['ip', 'ipv6', 'manager_key', 'language', 'country', 'city', 'isp', 'asn', 'os', 'browser', 'external_uclick'], 'string', 'max' => 50],
            [['ua', 'referer', 'referer_prelanding', 'detailed'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'at_datetime' => Yii::t('app', 'At Datetime'),
            'ip' => Yii::t('app', 'Ip'),
            'ipv6' => Yii::t('app', 'Ipv6'),
            'ua' => Yii::t('app', 'Ua'),
            'referer' => Yii::t('app', 'Referer'),
            'referer_prelanding' => Yii::t('app', 'Referer Prelanding'),
            'manager_key' => Yii::t('app', 'Manager Key'),
            'language' => Yii::t('app', 'Language'),
            'country' => Yii::t('app', 'Country'),
            'city' => Yii::t('app', 'City'),
            'isp' => Yii::t('app', 'Isp'),
            'asn' => Yii::t('app', 'Asn'),
            'os' => Yii::t('app', 'Os'),
            'browser' => Yii::t('app', 'Browser'),
            'external_uclick' => Yii::t('app', 'External Uclick'),
            'log_type' => Yii::t('app', 'Log Type'),
            'detailed' => Yii::t('app', 'Detailed'),
            'is_bot' => Yii::t('app', 'Is Bot'),
            'meta_data' => Yii::t('app', 'Meta Data'),
        ];
    }
}
