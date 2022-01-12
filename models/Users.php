<?php

namespace app\models;

use app\basic\debugHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $confirmation_token
 * @property int $status
 * @property int|null $superadmin
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $registration_ip
 * @property string|null $bind_to_ip
 * @property string|null $email
 * @property int $email_confirmed
 * @property string|null $display_name
 * @property int $balance
 * @property int $limit
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 * @property UserVisitLog[] $userVisitLogs
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'created_at', 'updated_at'], 'required'],
            [['status', 'superadmin', 'created_at', 'updated_at', 'email_confirmed', 'balance', 'limit'], 'integer'],
            [['username', 'password_hash', 'confirmation_token', 'bind_to_ip', 'display_name'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['registration_ip'], 'string', 'max' => 15],
            [['email'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'confirmation_token' => 'Confirmation Token',
            'status' => 'Status',
            'superadmin' => 'Superadmin',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'registration_ip' => 'Registration Ip',
            'bind_to_ip' => 'Bind To Ip',
            'email' => 'Email',
            'email_confirmed' => 'Email Confirmed',
            'display_name' => 'Display Name',
            'balance' => 'Balance',
            'limit' => 'Limit',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemNames()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('{{%auth_assignment}}', ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserVisitLogs()
    {
        return $this->hasMany(UserVisitLog::className(), ['user_id' => 'id']);
    }

    public static function getPartners()
    {
        $partner_ids = (new \yii\db\Query())
            ->select(['user_id'])
            ->from('auth_assignment')
            ->where(['item_name' => 'partner'])
            ->all();
        $partner_ids = ArrayHelper::map($partner_ids,'user_id', 'user_id');
        sort($partner_ids);
        return self::find()->where(['id' => $partner_ids])->all();
    }

    public static function getPartnersArray()
    {
        $partners = self::getPartners();
        $partners_array = [0 => ''];
        foreach($partners as $partner) {
            $partners_array[$partner->id] = $partner->display_name;
        }
        return $partners_array;
    }
}
