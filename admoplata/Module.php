<?php

namespace pavlinter\admoplata;

use pavlinter\adm\Adm;
use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;

/**
 * @property \pavlinter\admoplata\ModelManager $manager
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'pavlinter\admoplata\controllers';

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
     * @inheritdoc
     */
    public function bootstrap($adm)
    {
        /* @var $adm \pavlinter\adm\Adm */
        if ($adm->user->can('Adm-Pages')) {
            $adm->params['left-menu']['admoplata'] = [
                'label' => '<i class="fa fa-file-text"></i><span>' . $adm::t('admoplata','Oplata') . '</span>',
                'url' => ['/' . $adm->id . '/admoplata/page/index']
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        OplataAsset::register(Yii::$app->getView());
        return parent::beforeAction($action);
    }
}
