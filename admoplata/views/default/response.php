<?php
use pavlinter\admoplata\Module;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \pavlinter\admpages\models\Page */
/* @var $request array */
/* @var $isPaid boolean */

$admoplata = Module::getInstance();

$admoplata->layout = $admoplata->invoiceLayout;
?>

<?php Alert::begin([
    'closeButton' => false,
    'options' => [
        'class' => 'alert-success',
    ],
]) ?>

<?= Module::t('', "Thank you for shopping! Your {startLink}order{endLink}.", [
    'dot' => true,
    'startLink' => Html::beginTag('a', ['href' => Url::to(['default/invoice', 'alias' => $model->alias])]),
    'endLink' => Html::endTag('a'),
]); ?>

<?php Alert::end(); ?>
