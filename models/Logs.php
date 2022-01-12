<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_logs}}".
 *
 * @property int $id
 * @property string $user_ip
 * @property string $title
 * @property string $text
 * @property int $type
 * @property string|null $date
 */
class Logs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_ip', 'title', 'text', 'type'], 'required'],
            [['text'], 'string'],
            [['type'], 'integer'],
            [['date'], 'safe'],
            [['user_ip', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_ip' => 'User Ip',
            'title' => 'Title',
            'text' => 'Text',
            'type' => 'Type',
            'date' => 'Date',
        ];
    }
}
