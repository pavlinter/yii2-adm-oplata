<?php

namespace pavlinter\admoplata;

use pavlinter\adm\Adm;
use pavlinter\adm\AdmBootstrapInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \pavlinter\admoplata\ModelManager $manager
 */
class Module extends \yii\base\Module implements AdmBootstrapInterface
{
    public $controllerNamespace = 'pavlinter\admoplata\controllers';

    public $invoiceLayout = '/main';

    public $layout = '@vendor/pavlinter/yii2-adm/adm/views/layouts/main';

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
        parent::init();
        // custom initialization code goes here
    }

    /**
     * @param \pavlinter\adm\Adm $adm
     */
    public function loading($adm)
    {
        if ($adm->user->can('AdmRoot')) {
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
            OplataAsset::register(Yii::$app->getView());
        }
        return parent::beforeAction($action);
    }

}
