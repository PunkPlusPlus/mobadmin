<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tbl_blocking".
 *
 * @property int $id
 * @property int $block_type
 * @property int $block_method
 * @property string $block_value
 * @property int $block_position
 * @property string $block_params
 * @property string $active
 * @property string $deleted
 *
 * @property BlockType $blockType
 * @property BlockMethod $blockMethod
 */
class Blocking extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_blocking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['block_type', 'block_method', 'block_value'], 'required'],
            [['block_type', 'block_method', 'block_position'], 'integer'],
            [['active', 'deleted'], 'string'],
            [['block_value', 'block_params'], 'string', 'max' => 255],
            [['block_type'], 'exist', 'skipOnError' => true, 'targetClass' => BlockType::className(), 'targetAttribute' => ['block_type' => 'id']],
            [['block_method'], 'exist', 'skipOnError' => true, 'targetClass' => BlockMethod::className(), 'targetAttribute' => ['block_method' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'block_type' => Yii::t('app', 'Block Type'),
            'block_method' => Yii::t('app', 'Block Method'),
            'block_value' => Yii::t('app', 'Block Value'),
            'block_position' => Yii::t('app', 'Block Position'),
            'block_params' => Yii::t('app', 'Block Params'),
            'active' => Yii::t('app', 'Active'),
            'deleted' => Yii::t('app', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlockType()
    {
        return $this->hasOne(BlockType::className(), ['id' => 'block_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlockMethod()
    {
        return $this->hasOne(BlockMethod::className(), ['id' => 'block_method']);
    }
}
