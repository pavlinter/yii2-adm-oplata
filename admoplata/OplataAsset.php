<?php

/**
 * @package yii2-adm-oplata
 * @author Pavels Radajevs <pavlinter@gmail.com>
 * @copyright Copyright &copy; Pavels Radajevs <pavlinter@gmail.com>, 2015
 * @version 1.0.0
 */

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