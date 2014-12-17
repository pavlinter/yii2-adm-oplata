<?php

namespace pavlinter\admoplata;

/**
 * Class PageAsset
 */
class OplataAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/pavlinter/yii2-adm-pages/admoplata/assets';
    public $css = [

    ];
    public $js = [
        'js/common.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}