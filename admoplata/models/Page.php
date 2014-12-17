<?php

namespace pavlinter\admpages\models;

use pavlinter\admpages\Module;
use Yii;
use pavlinter\translation\TranslationBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%page}}".
 *
 * @method \pavlinter\translation\TranslationBehavior getLangModels
 * @method \pavlinter\translation\TranslationBehavior setLanguage
 * @method \pavlinter\translation\TranslationBehavior getLanguage
 * @method \pavlinter\translation\TranslationBehavior saveTranslation
 * @method \pavlinter\translation\TranslationBehavior saveAllTranslation
 * @method \pavlinter\translation\TranslationBehavior saveAll
 * @method \pavlinter\translation\TranslationBehavior validateAll
 * @method \pavlinter\translation\TranslationBehavior validateLangs
 * @method \pavlinter\translation\TranslationBehavior loadAll
 * @method \pavlinter\translation\TranslationBehavior loadLang
 * @method \pavlinter\translation\TranslationBehavior loadLangs
 * @method \pavlinter\translation\TranslationBehavior getTranslation
 * @method \pavlinter\translation\TranslationBehavior hasTranslation
 *
 * @property string $id
 * @property string $id_parent
 * @property string $layout
 * @property string $type
 * @property string $weight
 * @property integer $visible
 * @property integer $active
 * @property string $date
 *
 * Translation
 * @property string $name
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property string $image
 * @property string $alias
 * @property string $url
 * @property string $text
 *
 * @property PageLang[] $translations
 * @property Page $parent
 */
class Page extends \yii\db\ActiveRecord
{
    static $textBreak = '<div style="page-break-after: always"><span style="display:none">&nbsp;</span></div>';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
            ],
            'trans' => [
                'class' => TranslationBehavior::className(),
                'translationAttributes' => [
                    'name',
                    'title',
                    'description',
                    'keywords',
                    'image',
                    'alias',
                    'url',
                    'text',
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%page}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['weight', 'id_parent'], 'default', 'value' => null],
            [['id_parent', 'weight', 'visible', 'active'], 'integer'],
            [['layout', 'type'], 'required'],
            [['layout', 'type'], 'string', 'max' => 50],
            [['date'], 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
            [['layout'], 'in', 'range' => array_keys(Module::getInstance()->pageLayouts)],
            [['type'], 'in', 'range' => array_keys(Module::getInstance()->pageTypes)],
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
            'id_parent' => Yii::t('adm/admpages', 'Parent'),
            'layout' => Yii::t('adm/admpages', 'Layout'),
            'weight' => Yii::t('adm/admpages', 'Weight'),
            'visible' => Yii::t('adm/admpages', 'Visible'),
            'active' => Yii::t('adm/admpages', 'Active'),
            'date' => Yii::t('adm/admpages', 'Date'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($this->weight === null) {
            $query = self::find()->select(['MAX(weight)']);
            if (!$insert) {
                $query->where(['!=', 'id', $this->id]);
            }
            $this->weight = $query->scalar() + 50;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @param bool $onlyshort
     * @return bool|string
     */
    public function shortText($onlyshort = false)
    {
        $pos = strpos($this->text, self::$textBreak);
        if ($pos !== false) {
            return \yii\helpers\StringHelper::truncate($this->text, $pos, null);
        }
        if ($onlyshort) {
            return false;
        }
        return $this->text;
    }

    /**
     * @param null $encoding
     * @return string
     */
    public function text($encoding = null)
    {
        $pos = strpos($this->text, self::$textBreak);
        if ($pos !== false) {
            return mb_substr($this->text, $pos, null, $encoding ?: Yii::$app->charset);
        }
        return $this->text;
    }

    /**
     * @param $url
     * @param null $id_language
     * @param string $key
     * @return mixed
     */
    public function url($url, $id_language = null, $key = 'alias')
    {
        return $this->getTranslation($id_language)->url($url, $key);
    }

    /**
     * @param $id
     * @param array $config
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public static function get($id, $config = [])
    {
        Yii::$app->getModule('adm'); // load module
        $config = ArrayHelper::merge([
            'type' => 'page',
            'setLanguageUrl' => true,
            'registerMetaTag' => true,
            'where' => false,
            'orderBy' => false,
        ], $config);



        $pageTable = forward_static_call(array(Module::getInstance()->manager->pageClass, 'tableName'));
        
        $query = self::find()->from(['p' => $pageTable])->innerJoinWith(['translations']);
        if ($config['where'] === false) {
            $query->where(['p.id' => $id]);
        } else {
            $query->where($config['where']);
        }
        if ($config['orderBy'] !== false) {
            $query->orderBy($config['orderBy']);
        }

        $model = $query->one();

        if ($model === null) {
            return null;
        }

        if (!$model->active || !isset($model->translations[Yii::$app->getI18n()->getId()])) {
            return false;
        }

        if ($config['setLanguageUrl']) {
            if (!isset($config['url'])) {
                $url = [''];
            } else {
                $url = $config['url'];
            }
            
            foreach (Yii::$app->getI18n()->getLanguages() as $id_language => $language) {
                if (is_array($url)) {
                    $language['url'] = ArrayHelper::merge($url, [
                        'lang' => $language[Yii::$app->getI18n()->langColCode],
                    ]);
                    $language['url'] = Yii::$app->getUrlManager()->createUrl($language['url']);
                } elseif (is_callable($url)) {
                    $language['url'] = call_user_func($url, $model, $id_language, $language);
                }

                Yii::$app->getI18n()->setLanguage($id_language, $language);
            }
        }
        if ($config['registerMetaTag']) {
            Yii::$app->getView()->registerMetaTag(['name' => 'description', 'content' => $model->description]);
            Yii::$app->getView()->registerMetaTag(['name' => 'keywords', 'content' => $model->keywords]);
        }
        return $model;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(Module::getInstance()->manager->pageLangClass, ['page_id' => 'id'])->indexBy('language_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Module::getInstance()->manager->pageClass, ['id' => 'id_parent']);
    }
}
