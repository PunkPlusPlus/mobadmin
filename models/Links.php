<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%tbl_links}}".
 *
 * @property int $id
 * @property int $linkcountry_id
 * @property int $user_id
 * @property string $key
 * @property string $value
 * @property string $url
 * @property int|null $archived
 */
class Links extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_links}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['linkcountry_id', 'key', 'value'], 'required'],
            [['linkcountry_id', 'user_id', 'archived', 'is_main'], 'integer'],
            [['key', 'value', 'url'], 'string', 'max' => 255],
            [['label'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'is_main' => Yii::t('app', 'Is Main'),
            'linkcountry_id' => Yii::t('app', 'Linkcountry ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'url' => Yii::t('app', 'Url'),
            'archived' => Yii::t('app', 'Archived'),
            'label' => Yii::t('app', 'label'),
        ];
    }

    public function getLinkcountry()
    {
        return $this->hasOne(Linkcountries::className(), ['id' => 'linkcountry_id']);
    }
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    public static function getMainLink($countryInfo)
    {
        $linksInfo = Links::find()
            ->where(['linkcountry_id' => $countryInfo['id']])
            ->andWhere(['is_main' => 1])
            ->andWhere(['archived' => 0])
            ->one();
        return $linksInfo;
    }

    public static function getMainLinks($app)
    {
        $links = array();
        $linkcountries = Linkcountries::find()
            ->where(['app_id' => $app->id])
            ->andWhere(['archived' => 0])->all();
        foreach ($linkcountries as $country) {
            $link = Links::find()
                ->where(['linkcountry_id' => $country->id])
                ->andWhere(['archived' => 0])
                ->andWhere(['is_main' => 1])
                ->one();
            array_push($links, $link);
        }
        return $links;
    }

    private static function findBlank($linkcountry_id)
    {
        $blank = self::find()
            ->where(['linkcountry_id' => $linkcountry_id])
            ->andWhere(['archived' => 0])
            ->andWhere(['user_id' => 48])->one();
        if (!$blank || $blank->archived == 1) return false;
        return $blank;

    }

    public static function deleteBlank($app)
    {
        $mainLinks = self::getMainLinks($app);
        foreach ($mainLinks as $main) {
            if ($main->user_id = 48 && $main->url = "block" && $main->archived == 0) {
                $link = Links::find()->where(['id' => intval($main->value)])->one();
                if ($link) {
                    $link->is_main = 1;
                    //$main->is_main = 0;
                    //$main->save();
                    $link->save();
                    $main->delete();
                }
            }
        }
    }

    public static function createBlank($app)
    {
        $mainLinks = self::getMainLinks($app);
        foreach ($mainLinks as $main) {
            $blank = self::findBlank($main->linkcountry_id);
            if (!$blank) $blank = new Links();
            $blank->key = "camp_name";
            $blank->linkcountry_id = $main->linkcountry_id;
            $blank->user_id = 48;
            $blank->url = "block";
            $blank->archived = 0;
            $blank->value = strval($main->id);
            $blank->is_main = 1;
            $main->is_main = 0;
            $main->save();
            $blank->save();
        }
        return true;
    }

}
