<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Apps;
use app\models\Linkcountries;
use app\models\Links;
use app\basic\debugHelper;
use webvimark\modules\UserManagement\models\User;

/**
 * AppsSearch represents the model behind the search form of `app\models\Apps`.
 */
class AppsSearch extends Apps
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'published'], 'integer'],
            [['name', 'package'], 'safe'],
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
    public function search($params)
    {
        $query = Apps::find();


        $where = ['OR'];
        //debugHelper::print(User::hasPermission('view_all_apps'));
        if (!User::hasPermission('view_all_apps')) {

            $linkList = Links::find()
                ->where(['=','user_id', User::getCurrentUser()->id])
                ->andWhere(['=','archived', 0])
                ->all();

            $i = 0;
            foreach ($linkList as $link) {
                array_push($where, ["id" => $link->linkcountry->app_id]);
                $i++;
            }
            if ($i == 0) {
                array_push($where, ["id" => -9999]);
            }
        }else{
            if(isset($_GET['user_id']) && strlen($_GET['user_id']) > 0) {
                $linkList = Links::find()
                    ->where(['=','user_id', $params['user_id']])
                    ->andWhere(['=','archived', 0])
                    ->all();


                $i = 0;
                foreach ($linkList as $link) {

                    $linkCountry = Linkcountries::find()
                        ->where(['=','id', $link->linkcountry_id])
                        ->one();

                    array_push($where, ["id" => $linkCountry->app_id]);
                    $i++;
                }
                if ($i == 0) {
                    array_push($where, ["id" => -9999]);
                }
            }

            if(isset($_GET['created_code_user_id']) && strlen($_GET['created_code_user_id']) > 0) {
                $allAppsUser = Apps::find()->where(["created_code_user_id" => $_GET['created_code_user_id']])->all();

                $i = 0;
                foreach ($allAppsUser as $app) {
                    array_push($where, ["id" => $app->id]);
                    $i++;
                }
                if ($i == 0) {
                    array_push($where, ["id" => -9999]);
                }
            }

            if(isset($_GET['builder_code_user_id']) && strlen($_GET['builder_code_user_id']) > 0) {
                $allAppsUser = Apps::find()->where(["builder_code_user_id" => $_GET['builder_code_user_id']])->all();

                $i = 0;
                foreach ($allAppsUser as $app) {
                    array_push($where, ["id" => $app->id]);
                    $i++;
                }
                if ($i == 0) {
                    array_push($where, ["id" => -9999]);
                }
            }
        }

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'published' => $this->published,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'package', $this->package]);

        $query->orderBy(['id' => SORT_ASC]);

        return $dataProvider;
    }
}
