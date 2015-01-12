<?php

namespace pavlinter\admoplata;

use pavlinter\adm\Adm;
use pavlinter\adm\AdmBootstrapInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @property \pavlinter\admoplata\ModelManager $manager
 */
class Module extends \yii\base\Module implements AdmBootstrapInterface
{
    public $controllerNamespace = 'pavlinter\admoplata\controllers';

    public $invoiceLayout = '/main';

    public $layout = '@vendor/pavlinter/yii2-adm/adm/views/layouts/main';

    public $userSelect = [];

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, $config = [])
    {
        $config = ArrayHelper::merge([
            'components' => [
                'manager' => [
                    'class' => 'pavlinter\admoplata\ModelManager'
                ],
            ],
        ], $config);


        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        if(!isset($this->userSelect['viewCallback'])){
            $this->userSelect['viewCallback'] = function ($row) {
                return $row['email'];
            };
        }
        if(!isset($this->userSelect['querySearch'])){
            $this->userSelect['querySearch'] = function ($userTable, $search) {
                $query = new \yii\db\Query();
                return $query->from($userTable)
                    ->where('email LIKE "%' . $search .'%"')
                    ->limit(20)->all();
            };
        }

        if(!isset($this->userSelect['queryLoad'])){
            $this->userSelect['queryLoad'] = function ($userTable, $id) {
                $query = new \yii\db\Query();
                return $query->from($userTable)
                    ->where(['id' => $id])->one();
            };
        }

        if (!is_callable($this->userSelect['viewCallback'])) {
            throw new InvalidConfigException('The "viewCallback" property must be callable.');
        }
        if (!is_callable($this->userSelect['querySearch'])) {
            throw new InvalidConfigException('The "querySearch" property must be callable.');
        }
        if (!is_callable($this->userSelect['queryLoad'])) {
            throw new InvalidConfigException('The "viewCallback" property must be callable.');
        }

        parent::init();
        // custom initialization code goes here
    }

    /**
     * @param \pavlinter\adm\Adm $adm
     */
    public function loading($adm)
    {
        if ($adm->user->can('Adm-OplataRead')) {
            $adm->params['left-menu']['admoplata'] = [
                'label' => '<i class="fa fa-usd"></i><span>' . $adm::t('menu','Oplata') . '</span>',
                'url' => ['/admoplata/transaction/index'],
                'visible' => $adm->user->can('Adm-OplataRead'),
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->controller->id !== 'default') {
            Adm::register(); //required load adm,if use adm layout
        }
        OplataAsset::register(Yii::$app->getView());
        return parent::beforeAction($action);
    }

    /**
     *
     */
    public function registerTranslations()
    {
        if (!isset(Yii::$app->i18n->translations['admoplata*'])) {
            Yii::$app->i18n->translations['admoplata*'] = [
                'class' => 'pavlinter\translation\DbMessageSource',
                'forceTranslation' => true,
                'autoInsert' => true,
                'dotMode' => true,
            ];
        }
    }
    /**
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if ($category) {
            $category = 'admoplata/' . $category;
        } else {
            $category = 'admoplata';
        }
        return Yii::t($category, $message, $params, $language);
    }

}
