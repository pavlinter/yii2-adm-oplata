<?php

namespace pavlinter\admpages\models;

use pavlinter\admpages\Module;
use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%page_lang}}".
 *
 * @property string $id
 * @property string $page_id
 * @property integer $language_id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property string $image
 * @property string $alias
 * @property string $text
 *
 * @property Language $language
 * @property Page $page
 */
class PageLang extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%page_lang}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'title', 'description', 'keywords', 'url'], 'filter', 'filter' => function ($value) {
                return Html::encode($value);
            }],
            [['name'], 'required'],
            [['page_id', 'language_id'], 'integer'],
            [['text'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 80],
            [['url'], 'string', 'max' => 2000],
            [['description', 'image', 'alias'], 'string', 'max' => 200],
            [['keywords'], 'string', 'max' => 250],
            [['alias'], 'match', 'pattern' => '/^([A-Za-z0-9_-])+$/'],
            [['alias'], 'unique', 'filter' => function ($query) {
                if (!$this->isNewRecord) {
                    $query->andWhere(['!=', 'page_id', $this->page_id]);
                }
                return $query;
            }],
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm/admpages', 'ID'),
            'page_id' => Yii::t('adm/admpages', 'Page ID'),
            'language_id' => Yii::t('adm/admpages', 'Language ID'),
            'name' => Yii::t('adm/admpages', 'Name'),
            'title' => Yii::t('adm/admpages', 'Title'),
            'description' => Yii::t('adm/admpages', 'Description'),
            'keywords' => Yii::t('adm/admpages', 'Keywords'),
            'image' => Yii::t('adm/admpages', 'Image'),
            'alias' => Yii::t('adm/admpages', 'Alias'),
            'text' => Yii::t('adm/admpages', 'Text'),
        ];
    }

    /**
     * @param $url
     * @param string $key
     * @return mixed
     */
    public function url($url, $key = 'alias')
    {
        if ($this->url) {
            return $this->url;
        }
        $url[$key] = $this->alias;
        return $url;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Module::getInstance()->manager->pageClass, ['id' => 'page_id']);
    }
}
