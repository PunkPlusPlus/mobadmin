<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_user_profiles}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $salary
 * @property float|null $bonus_factor
 */
class EmployeeProfiles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_employee_profiles}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'salary'], 'integer'],
            [['bonus_factor'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'salary' => Yii::t('app', 'Salary'),
            'bonus_factor' => Yii::t('app', 'Bonus Factor'),
        ];
    }
}
