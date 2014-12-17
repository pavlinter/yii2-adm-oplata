<?php

namespace pavlinter\admpages\models;

use pavlinter\admpages\Module;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PageSearch represents the model behind the search form about `app\models\Page`.
 */
class PageSearch extends Page
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'weight', 'visible', 'active'], 'integer'],
            [['name', 'title', 'alias','type','id_parent'], 'string'],
            [['layout'], 'safe'],
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
    public function search($params,$id_parent)
    {
        $pageTable      = forward_static_call(array(Module::getInstance()->manager->pageClass, 'tableName'));
        $pageLangTable  = forward_static_call(array(Module::getInstance()->manager->pageLangClass, 'tableName'));


        $query = self::find()->from(['p' => $pageTable])
            ->innerJoin(['l'=> $pageLangTable],'l.page_id=p.id AND l.language_id=:language_id',[':language_id' => Yii::$app->getI18n()->getId()]);

        $loadParams = $this->load($params);
        if ($this->id_parent) {
            $query->innerJoin(['l2'=> $pageLangTable],'l2.page_id=p.id_parent AND l2.language_id=:language_id',[':language_id' => Yii::$app->getI18n()->getId()]);
        }

        $query->with([
            'parent',
            'translations',
        ]);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'weight' => SORT_ASC,
                ],
            ],
        ]);

        if ($id_parent !== false) {
            if (empty($id_parent)) {
                $query->where(['id_parent' => null]);
            } else {
                $query->where(['id_parent' => $id_parent]);
            }
        }
        if (!($loadParams && $this->validate())) {
            return $dataProvider;
        }


        $dataProvider->sort->attributes['name']['asc'] = ['l.name' => SORT_ASC];
        $dataProvider->sort->attributes['name']['desc'] = ['l.name' => SORT_DESC];

        $dataProvider->sort->attributes['title']['asc'] = ['l.title' => SORT_ASC];
        $dataProvider->sort->attributes['title']['desc'] = ['l.title' => SORT_DESC];

        $dataProvider->sort->attributes['alias']['asc'] = ['l.alias' => SORT_ASC];
        $dataProvider->sort->attributes['alias']['desc'] = ['l.alias' => SORT_DESC];


        $query->andFilterWhere([
            'p.id' => $this->id,
            'p.weight' => $this->weight,
            'p.layout' => $this->layout,
            'p.type' => $this->type,
            'p.visible' => $this->visible,
            'p.active' => $this->active,
        ]);

        $query->andFilterWhere(['like', 'l.name', $this->name])
            ->andFilterWhere(['like', 'l.title', $this->title])
            ->andFilterWhere(['like', 'l.alias', $this->alias]);

        if ($this->id_parent) {
            $query->andFilterWhere(['like', 'l2.name', $this->id_parent]);
        }


        return $dataProvider;
    }
}
