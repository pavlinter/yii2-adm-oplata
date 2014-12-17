<?php

namespace pavlinter\admoplata\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OplataTransactionSearch represents the model behind the search form about `pavlinter\admoplata\models\OplataTransaction`.
 */
class OplataTransactionSearch extends OplataTransaction
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'payment_id'], 'integer'],
            [['email', 'currency', 'order_status', 'response_status', 'data', 'response_data', 'alias', 'created_at'], 'safe'],
            [['price', 'shipping'], 'number'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'payment_id' => $this->payment_id,
            'price' => $this->price,
            'shipping' => $this->shipping,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'order_status', $this->order_status])
            ->andFilterWhere(['like', 'response_status', $this->response_status])
            ->andFilterWhere(['like', 'data', $this->data])
            ->andFilterWhere(['like', 'response_data', $this->response_data])
            ->andFilterWhere(['like', 'alias', $this->alias]);

        return $dataProvider;
    }
}