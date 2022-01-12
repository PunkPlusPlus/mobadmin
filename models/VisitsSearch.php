<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Visits;
use app\basic\debugHelper;
use app\basic\genKeyHelper;
use webvimark\modules\UserManagement\models\User;

/**
 * VisitsSearch represents the model behind the search form of `app\models\Visits`.
 */
class VisitsSearch extends Visits
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'link_id', 'device_id', 'cloaking'], 'integer'],
            [['extra', 'server_response', 'date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $app_id, $selectCountry, $selectLinks, $selectLabels, $selectUsers, $from, $to)
    {
        //debugHelper::print($params);
        //$query = Visits::find();

        if (!$selectCountry) {
            $linkCountries = Linkcountries::find();
            $linkCountries->where(['app_id' => $app_id]);
            $linkCountries = $linkCountries->all();

            $whereLink = ['OR'];
            foreach ($linkCountries as $value) {
                array_push($whereLink, ["linkcountry_id" => $value["id"]]);
            }
        } else {
            $whereLink = ['OR'];
            foreach ($selectCountry as $linkId) {
                array_push($whereLink, ["linkcountry_id" => $linkId]);
            }
        }

        $namings = Namings::find()->where(['app_id' => $app_id])->all();
        foreach ($namings as $naming) {
            array_push($whereLink, ['id' => $naming->link_id]);
        }


        $links = Links::find();
        $links->where($whereLink);
        if (!User::hasPermission('view_all_statistics')) {
            $links->andWhere(['user_id' => User::getCurrentUser()->id]);
        }

        $links = $links->all();

        $where = ['OR'];

        $connection = Yii::$app->getDb();
        if (!$selectLinks) {
            if ($selectLabels) {
                $i = 0;
                $whereLinks = " AND (";
                foreach ($selectLabels as $label) {
                    $label = Yii::$app->db->quoteValue($label);
                    if ($i > 0) {
                        $whereLinks .= " OR ";
                    }
                    if($label == "'NULL'"){
                        $whereLinks .= "tbl_links.label IS NULL";
                    }else{
                        $whereLinks .= "tbl_links.label = " . $label;
                    }
                    $i++;
                }
                $whereLinks .= ")";

                $whereCountry = "";
                if($selectCountry) {
                    $i = 0;
                    $whereCountry = " AND (";
                    foreach ($selectCountry as $countryId) {
                        $countryId = intval($countryId);
                        if ($i > 0) {
                            $whereCountry .= " OR ";
                        }
                        $whereCountry .= "tbl_links.linkcountry_id = " . $countryId;
                        $i++;
                    }
                    $whereCountry .= ")";
                }
                if ($i == 0) {
                    $whereCountry = "";
                }

                $whereUsers = "";
                if($selectUsers){
                    $i=0;
                    $whereUsers = " AND (";
                    foreach ($selectUsers as $userid) {
                        $userid = intval($userid);
                        if ($i > 0) {
                            $whereUsers .= " OR ";
                        }
                        $whereUsers .= "tbl_links.user_id = " . $userid;
                        $i++;
                    }
                    $whereUsers .= ")";
                }


                if (!User::hasPermission('view_all_statistics')) {
                        $linksLabel = $connection->createCommand("
                        SELECT
                            tbl_links.id 
                        FROM
                            tbl_links
                            INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id 
                        WHERE
                            tbl_links.archived = 0 
                            AND tbl_links.user_id = :user_id 
                            AND tbl_linkcountries.app_id = :app_id" . $whereLinks . $whereCountry . "
                        ", [':user_id' => User::getCurrentUser()->id, ':app_id' => $app_id]);
                }else{
                    $linksLabel = $connection->createCommand("
                        SELECT
                            tbl_links.id 
                        FROM
                            tbl_links
                            INNER JOIN tbl_linkcountries ON tbl_links.linkcountry_id = tbl_linkcountries.id 
                        WHERE
                            tbl_links.archived = 0 
                            AND tbl_linkcountries.app_id = :app_id" . $whereLinks . $whereCountry . $whereUsers."
                        ", [':app_id' => $app_id]);
                }
                $linksLabel = $linksLabel->queryAll();
                for ($i = 0; $i < count($linksLabel); $i++) {
                    array_push($where, ["link_id" => $linksLabel[$i]['id']]);
                }

                if (count($where) <= 1) {
                    array_push($where, ["link_id" => "-999999"]);
                }
            } else {
                foreach ($links as $value) {
                    array_push($where, ["link_id" => $value["id"]]);
                }

                if (count($where) <= 1) {
                    array_push($where, ["link_id" => "-999999"]);
                }
            }
        } else {

            $checkOR = ['OR'];
            $check = Links::find();
            if (!User::hasPermission('view_all_statistics')) {
                $check->andWhere(['user_id' => User::getCurrentUser()->id]);
            }

            foreach ($selectLinks as $linkId) {
                array_push($checkOR, ["id" => $linkId]);
            }

            $check->andWhere($checkOR);
            if ($check = $check->all()) {
                foreach ($check as $clink) {
                    array_push($where, ["link_id" => $clink->id]);
                }
            } else {
                die('404 Forbidden');
            }
        }


        $query = Visits::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->where($where);
        $query->andWhere(['between', 'date', $from, $to]);

        // grid filtering conditions

        $query->andFilterWhere([
            'id' => $this->id,
            //'linkcountry_id' => $this->linkcountry_id,
            'device_id' => $this->device_id,
            'cloaking' => $this->cloaking,
            'date' => $this->date,
        ]);

        $query->andFilterWhere(['like', 'extra', $this->extra])
            ->andFilterWhere(['like', 'server_response', $this->server_response]);

        return $dataProvider;
    }
}
