<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_balance".
 *
 * @property int $id
 * @property int $app_id
 * @property float $count
 * @property string $status
 * @property int|null $partner_id
 * @property string $comment
 * @property string $created_at
 *
 * @property Apps $app
 * @property Users $partner
 */
class AppBalance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'app_balance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'count', 'status'], 'required'],
            [['app_id', 'partner_id'], 'integer'],
            [['count'], 'number'],
            [['status', 'comment'], 'string'],
            [['created_at'], 'safe'],
            [['app_id'], 'exist', 'skipOnError' => true, 'targetClass' => Apps::class, 'targetAttribute' => ['app_id' => 'id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['partner_id' => 'id']],
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
            'count' => Yii::t('app', 'Count'),
            'status' => Yii::t('app', 'Status'),
            'partner_id' => Yii::t('app', 'Partner ID'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(Apps::class, ['id' => 'app_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Users::class, ['id' => 'partner_id']);
    }


}
