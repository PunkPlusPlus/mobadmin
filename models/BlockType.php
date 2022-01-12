<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tbl_block_type".
 *
 * @property int $id
 * @property string $varname 
 * @property string $name
 * @property string $active
 * @property string $deleted
 *
 * @property Blocking[] $tblBlockings
 */
class BlockType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_block_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['varname', 'name'], 'required'],
            [['active', 'deleted'], 'string'],
			[['varname'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
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
        return $this->hasMany(Blocking::className(), ['block_type' => 'id']);
    }
}
