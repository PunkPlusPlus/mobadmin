<?php


namespace app\models;

use Yii;
use app\basic\debugHelper;
use yii\helpers\ArrayHelper;

class PartnerBalance extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{partner_balance}}';
    }

    public function rules()
    {
        return [
            [['partner_id', 'balance'], 'required'],
            ['partner_id', 'integer'],
            ['balance', 'double'],
            ['money_limit', 'integer', 'max' => 0],
            ['is_banned', 'boolean'],
            ['last_update', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'partner_id' => Yii::t('app', 'partner_id'),
            'balance' => Yii::t('app', 'balance'),
            'last_update' => Yii::t('app', 'last_update'),
        ];
    }

    public static function checkNewPartners()
    {

        $partnerBalance = PartnerBalance::find()->select('partner_id')->all();
        $partnerBalance = ArrayHelper::map($partnerBalance,'partner_id', 'partner_id');
        sort($partnerBalance);

        $newPartners = (new \yii\db\Query())
            ->select(['user_id'])
            ->from('auth_assignment')
            ->where(['item_name' => 'partner'])
            ->andWhere(['NOT IN', 'user_id', $partnerBalance, false])
            ->all();

        foreach($newPartners as $partner) {
            $newPartner = new PartnerBalance();
            $newPartner->partner_id = $partner['user_id'];
            $newPartner->balance = 0.00;
            $newPartner->save();
        }
    }

    public static function checkIsBanned($partner)
    {
        if($partner->balance <= $partner->money_limit) {
            $partner->is_banned = 1;
            return 1;
        }
        $partner->is_banned = 0;
        return 0;
    }

    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'partner_id']);
    }
}