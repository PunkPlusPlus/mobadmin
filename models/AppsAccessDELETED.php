<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_apps_access}}".
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property string|null $date
 */
class AppsAccess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_apps_access}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_id', 'user_id'], 'required'],
            [['app_id', 'user_id'], 'integer'],
            [['date'], 'safe'],
            [['price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'user_id' => 'User ID',
            'date' => 'Date',
            'price' => Yii::t('app', 'price'),
        ];
    }


    public function getUserinfo(){
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
