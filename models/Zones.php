<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_zones}}".
 *
 * @property string $zone
 * @property string $country
 * @property string $Introduced
 * @property string $Region
 */
class Zones extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_zones}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['zone', 'country', 'Introduced', 'Region'], 'required'],
            [['zone'], 'string', 'max' => 20],
            [['country', 'Introduced', 'Region'], 'string', 'max' => 255],
            [['zone'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'zone' => 'Zone',
            'country' => 'Country',
            'Introduced' => 'Introduced',
            'Region' => 'Region',
        ];
    }

    public static function getZones(): array
    {
        $zones = self::find()->all();
        $listCountry = array();
        foreach ($zones as $zone) {
            array_push($listCountry, $zone->zone);
        }
        return $listCountry;
    }
}
