<?php

namespace app\models\power;

use app\models\base\BaseFormModel;
use Yii;

/**
 * This is the model class for table "lg_power_client_user_data".
 *
 * @property int $id
 * @property int $batch_id
 * @property int $at_datetime
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $password
 * @property string $phone
 * @property string $phonecc
 * @property int $partner_id
 * @property int|null $user_id
 * @property string|null $ext_id
 * @property string|null $click_id
 * @property string|null $country
 * @property string|null $region_code
 * @property string|null $city
 * @property string|null $status
 * @property string|null $result
 * @property int|null $lead
 * @property int|null $deposit
 * @property string|null $deposit_amount
 * @property int|null $ggl_tracker_id
 * @property int|null $ggl_client_id
 * @property int $ggl_sent_deposit
 * @property int|null $fb_client_id
 * @property int|null $fb_c_user
 * @property int|null $fb_pixel_id
 * @property int|null $fb_access_token
 *
 * @property LgPowerBatch $batch
 */
class LgPowerClientUserData extends BaseFormModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lg_power_client_user_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['batch_id', 'at_datetime', 'firstname', 'lastname', 'email', 'password', 'phone', 'phonecc', 'partner_id'], 'required'],
            [['batch_id', 'at_datetime', 'partner_id', 'user_id', 'lead', 'deposit', 'ggl_tracker_id', 'ggl_client_id', 'ggl_sent_deposit', 'fb_client_id', 'fb_c_user', 'fb_pixel_id', 'fb_access_token'], 'integer'],
            [['firstname', 'lastname', 'email', 'password', 'phone', 'ext_id', 'result'], 'string', 'max' => 255],
            [['phonecc', 'click_id', 'country', 'region_code', 'city', 'status', 'deposit_amount'], 'string', 'max' => 50],
            [['batch_id'], 'exist', 'skipOnError' => true, 'targetClass' => LgPowerBatch::className(), 'targetAttribute' => ['batch_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'batch_id' => Yii::t('app', 'Batch ID'),
            'at_datetime' => Yii::t('app', 'At Datetime'),
            'firstname' => Yii::t('app', 'Firstname'),
            'lastname' => Yii::t('app', 'Lastname'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'phone' => Yii::t('app', 'Phone'),
            'phonecc' => Yii::t('app', 'Phonecc'),
            'partner_id' => Yii::t('app', 'Partner ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'ext_id' => Yii::t('app', 'Ext ID'),
            'click_id' => Yii::t('app', 'Click ID'),
            'country' => Yii::t('app', 'Country'),
            'region_code' => Yii::t('app', 'Region Code'),
            'city' => Yii::t('app', 'City'),
            'status' => Yii::t('app', 'Status'),
            'result' => Yii::t('app', 'Result'),
            'lead' => Yii::t('app', 'Lead'),
            'deposit' => Yii::t('app', 'Deposit'),
            'deposit_amount' => Yii::t('app', 'Deposit Amount'),
            'ggl_tracker_id' => Yii::t('app', 'Ggl Tracker ID'),
            'ggl_client_id' => Yii::t('app', 'Ggl Client ID'),
            'ggl_sent_deposit' => Yii::t('app', 'Ggl Sent Deposit'),
            'fb_client_id' => Yii::t('app', 'Fb Client-ID'),
            'fb_c_user' => Yii::t('app', 'Fb C-User'),
            'fb_pixel_id' => Yii::t('app', 'Fb Pixel-ID'),
            'fb_access_token' => Yii::t('app', 'Fb Access-Token'),
        ];
    }

    /**
     * Gets query for [[Batch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBatch()
    {
        return $this->hasOne(LgPowerBatch::className(), ['id' => 'batch_id']);
    }
}
