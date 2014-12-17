<?php

namespace pavlinter\admoplata;

use pavlinter\adm\Manager;
use Yii;

/**
 * @method \pavlinter\admoplata\models\OplataItem createOplataItem
 * @method \pavlinter\admoplata\models\OplataItem createOplataItemQuery
 * @method \pavlinter\admoplata\models\OplataItem createOplataTransaction
 * @method \pavlinter\admoplata\models\OplataItem createOplataTransactionQuery
 * @method \pavlinter\admoplata\models\OplataItem createOplataTransactionSearch
 */
class ModelManager extends Manager
{
    /**
     * @var string|\pavlinter\admoplata\models\OplataItem
     */
    public $oplataItemClass = 'pavlinter\admpages\models\OplataItem';
    /**
     * @var string|\pavlinter\admoplata\models\OplataTransaction
     */
    public $oplataTransactionClass = 'pavlinter\admoplata\models\OplataTransaction';
    /**
     * @var string|\pavlinter\admoplata\models\OplataTransactionSearch
     */
    public $oplataTransactionSearchClass = 'pavlinter\admpages\models\OplataTransactionSearch';
}