<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tbl_block_method".
 *
 * @property int $id
 * @property string $defined_key
 * @property string $name
 * @property string $active
 * @property string $deleted
 *
 * @property Blocking[] $tblBlockings
 */
class BlockMethod extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_block_method';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['defined_key', 'name'], 'required'],
            [['active', 'deleted'], 'string'],
            [['defined_key', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'defined_key' => Yii::t('app', 'Defined Key'),
            'name' => Yii::t('app', 'Name'),
            'active' => Yii::t('app', 'Active'),
            'deleted' => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTblBlockings()
    {
        return $this->hasMany(Blocking::className(), ['block_method' => 'id']);
    }
}
