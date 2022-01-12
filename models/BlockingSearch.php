<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Blocking;

/**
 * BlockingSearch represents the model behind the search form of `app\models\Blocking`.
 */
class BlockingSearch extends Blocking
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'block_type', 'block_method', 'block_position'], 'integer'],
            [['block_value', 'block_params', 'active', 'deleted'], 'safe'],
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
        $query = Blocking::find();

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
            'block_type' => $this->block_type,
            'block_method' => $this->block_method,
            'block_position' => $this->block_position,
        ]);

        $query->andFilterWhere(['like', 'block_value', $this->block_value])
            ->andFilterWhere(['like', 'block_params', $this->block_params])
            ->andFilterWhere(['like', 'active', $this->active])
            ->andFilterWhere(['like', 'deleted', $this->deleted]);


        $params = Yii::$app->request->get('sort');
        if(is_null($params)) {
            $query->orderBy('id DESC');
        }

        return $dataProvider;
    }
}
