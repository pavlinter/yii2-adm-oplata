<?php

namespace pavlinter\admoplata;

use pavlinter\adm\Manager;
use Yii;

/**
 * @method \pavlinter\admoplata\models\Page createPage
 * @method \pavlinter\admoplata\models\Page createPageQuery
 * @method \pavlinter\admoplata\models\PageSearch createPageSearch
 * @method \pavlinter\admoplata\models\PageLang createPageLang
 * @method \pavlinter\admoplata\models\PageLang createPageLangQuery
 */
class ModelManager extends Manager
{
    /**
     * @var string|\pavlinter\admoplata\models\Page
     */
    public $pageClass = 'pavlinter\admpages\models\Page';
    /**
     * @var string|\pavlinter\admoplata\models\PageSearch
     */
    public $pageSearchClass = 'pavlinter\admpages\models\PageSearch';
    /**
     * @var string|\pavlinter\admoplata\models\PageLang
     */
    public $pageLangClass = 'pavlinter\admoplata\models\PageLang';
}