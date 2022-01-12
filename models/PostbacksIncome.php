<?php

namespace app\models;

//use app\models\base\BaseFormModel;
use app\models\base\BaseFormModel;
use Yii;

/**
 * This is the model class for table "tbl_postbacks_income".
 *
 * @property int $id
 * @property string|null $datetime
 * @property string|null $click_id
 * @property string|null $user_id
 * @property string|null $payout
 * @property string|null $country
 * @property string|null $partner_id
 * @property string|null $cnv_status
 * @property int|null $at_ldatet
 */
class PostbacksIncome extends BaseFormModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_postbacks_income';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['datetime'], 'safe'],
            [['at_ldatet'], 'integer'],
            [['click_id', 'user_id', 'payout', 'country', 'partner_id', 'cnv_status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'datetime' => Yii::t('app', 'Datetime'),
            'click_id' => Yii::t('app', 'Click ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'payout' => Yii::t('app', 'Payout'),
            'country' => Yii::t('app', 'Country'),
            'partner_id' => Yii::t('app', 'Partner ID'),
            'cnv_status' => Yii::t('app', 'Cnv Status'),
            'at_ldatet' => Yii::t('app', 'At Ldatet'),
        ];
    }
}
