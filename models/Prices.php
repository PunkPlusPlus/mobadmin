<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_prices}}".
 *
 * @property int $id
 * @property int $app_id
 * @property string $country_code
 * @property float $price
 * @property int|null $user_id
 */
class Prices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_prices}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'country_code', 'price'], 'required'],
            [['app_id', 'user_id'], 'integer'],
            [['price'], 'number'],
            [['country_code'], 'string', 'max' => 255],
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
            'country_code' => Yii::t('app', 'Country Code'),
            'price' => Yii::t('app', 'Price'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }
}
