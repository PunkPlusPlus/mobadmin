<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Log;

/**
 * LogSearch represents the model behind the search form of `app\models\Log`.
 */
class LogSearch extends Log
{

    public $managerKeys = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_bot'], 'integer'],
            [['at_datetime', 'ip', 'ipv6', 'ua', 'referer', 'referer_prelanding', 'manager_key', 'language', 'country', 'city', 'isp', 'asn', 'os', 'browser', 'external_uclick', 'log_type', 'detailed', 'meta_data'], 'safe'],
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
        $query = Log::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'at_datetime' => $this->at_datetime,
            'is_bot' => $this->is_bot,
        ]);

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'ipv6', $this->ipv6])
            ->andFilterWhere(['like', 'ua', $this->ua])
            ->andFilterWhere(['like', 'referer', $this->referer])
            ->andFilterWhere(['like', 'referer_prelanding', $this->referer_prelanding])
            ->andFilterWhere(['like', 'manager_key', $this->manager_key])
            ->andFilterWhere(['like', 'language', $this->language])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'isp', $this->isp])
            ->andFilterWhere(['like', 'asn', $this->asn])
            ->andFilterWhere(['like', 'os', $this->os])
            ->andFilterWhere(['like', 'browser', $this->browser])
            ->andFilterWhere(['like', 'external_uclick', $this->external_uclick])
            ->andFilterWhere(['like', 'log_type', $this->log_type])
            ->andFilterWhere(['like', 'detailed', $this->detailed])
            ->andFilterWhere(['like', 'meta_data', $this->meta_data]);


        $query->andFilterWhere(['in', 'manager_key', $this->managerKeys, false]);

        $params = Yii::$app->request->get('sort');
        if(is_null($params)) {
            $query->orderBy('at_datetime DESC');
        }

        return $dataProvider;
    }
}
