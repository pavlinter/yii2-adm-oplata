<?php

/**
 * @package yii2-adm-oplata
 * @author Pavels Radajevs <pavlinter@gmail.com>
 * @copyright Copyright &copy; Pavels Radajevs <pavlinter@gmail.com>, 2015
 * @version 1.0.0
 */

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

    public $userSelect = [
        'viewCallback' => null, //function ($row) {return Adm::t('oplata','{email}:select2 template', $row);}
        'querySearch' => null, // function ($query, $userTable, $search) {/* @var \yii\db\Query $query */return $query->from($userTable)->where(['like', 'email', $search])->limit(20)->all();}
        'queryLoad' => null, //function ($query, $userTable, $id) {/* @var \yii\db\Query $query */return $query->from($userTable)->where(['id' => $id])->one();}
    ];

    public $sendFunc = null; //function ($model, $module, $user, $username) {}

    public $sendFrom = null; // default Yii::$app->params['adminEmail']

    public $mailTemplate = "@vendor/pavlinter/yii2-adm-oplata/admoplata/views/transaction/email-template";

    public $remindTemplate = "@vendor/pavlinter/yii2-adm-oplata/admoplata/views/transaction/remind-template";

    public $pdf = [
        'image' => [ //htmlOptions
            //'src' => '',
        ],
        'imageLink' => [ //htmlOptions
            //'href' => '',
        ],
    ];

    public $remindDays = 1;

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
        if ($this->userSelect['viewCallback'] !== null && !is_callable($this->userSelect['viewCallback'])) {
            throw new InvalidConfigException('The "viewCallback" property must be callable.');
        }
        if ($this->userSelect['querySearch'] !== null && !is_callable($this->userSelect['querySearch'])) {
            throw new InvalidConfigException('The "querySearch" property must be callable.');
        }
        if ($this->userSelect['queryLoad'] !== null && !is_callable($this->userSelect['queryLoad'])) {
            throw new InvalidConfigException('The "queryLoad" property must be callable.');
        }

        if($this->sendFrom === null){
            $this->sendFrom = Yii::$app->params['adminEmail'];
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
