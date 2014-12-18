<?php

namespace pavlinter\admoplata;

/**
 * Class PageAsset
 */
class OplataAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/pavlinter/yii2-adm-oplata/admoplata/assets';
    public $css = [
        'css/invoice.css',
    ];
    public $js = [
        'js/common.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}