<?php

namespace app\models;

use Yii;
use webvimark\modules\UserManagement\models\User;


/**
 * This is the model class for table "{{%tbl_linkcountries}}".
 *
 * @property int $id
 * @property string $country_code
 * @property int $user_id
 * @property string $url
 * @property string|null $extra
 */
class Linkcountries extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tbl_linkcountries}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['country_code'], 'required'],
            [['archived'], 'integer'],
            [['app_id'], 'integer'],
            [['extra'], 'string'],
            [['country_code'], 'string', 'max' => 10],
            //[['yametrica_key'], 'string', 'max' => 36, 'min' => 36],
            //[['binom_key', 'yametrica_key'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => Yii::t('app', 'country'),
            'app_id' => Yii::t('app', 'app'),
            'extra' => Yii::t('app', 'params'),
            'archived' => Yii::t('app', 'archived'),
        ];
    }
    public function getActivelinks()
    {
        return $this->hasMany(Links::className(), ['linkcountry_id' => 'id'])
                    ->andOnCondition(['archived' => 0]);
    }
    public function getLinks()
    {
        return $this->hasMany(Links::className(), ['linkcountry_id' => 'id']);
    }

	
    public function getApp()
    {
        return $this->hasOne(Apps::className(), ['id' => 'app_id']);
    }

    public static function createMany($countries, $app, $existCountries, $listCountry)
    {       
        $invalidGeo = array();
        foreach ($countries as $geo) {
            $geo = strtolower($geo);
            $linkcountry = new Linkcountries();
            $linkcountry->app_id = $app->id;
            $linkcountry->country_code = $geo;
            if (!in_array($geo, $listCountry) || in_array($geo, $existCountries) || !$linkcountry->save()) {
                array_push($invalidGeo, $geo);
            }
           // $link = new Links();
           // $link->key = 'camp_name';
           // $link->linkcountry_id = $linkcountry->id;
           // $link->user_id = User::getCurrentUser()->id;
           // $link->url = "block";
           // $link->archived = 0;
           // $link->value = "Organic";
           // $link->is_main = 1;
           // $link->save();
        }
        return $invalidGeo;
    }


    public static function selectByApp($app_id)
    {
        $countries = self::find()
            ->where(['app_id' => $app_id])
            ->andWhere(['archived' => 0])
            ->all();
        $codes = array();
        if ($countries == null) return false;
        foreach ($countries as $country) {
            array_push($codes, $country->country_code);
        }
        return $codes;
    }

    public function blockOrganic($user)
    {
        $organic = Links::find()
            ->where(['linkcountry_id' => $this->id])
            ->andWhere(['is_main' => 1])
            ->andWhere(['archived' => 0])
            ->one();
        if ($organic) return false;
        $link = new Links();
        $link->key = 'camp_name';
        $link->linkcountry_id = $this->id;
        $link->user_id = $user->id;
        $link->label = $link->value;
        $link->url = "block";
        $link->archived = 0;
        $link->is_main = 1;
        $link->value = "Organic";
        $link->save();
        return true;
    }

	
    public function getVisits()
    {
		if(isset($_GET['country']) && $_GET['country'] != 'all'){
			return $this->hasMany(Visits::className(), ['linkcountry_id' => 'id'])
					->andOnCondition(['country_code' => $_GET['country']])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(15);
		}else{
			return $this->hasMany(Visits::className(), ['linkcountry_id' => 'id'])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(15);;
		}
    }
}
 
