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

$this->registerJs('
    $("#returnform").submit();
');

?>

<?php if ($isPaid) {?>

    <?php Alert::begin([
        'closeButton' => false,
        'options' => [
             'class' => 'alert-success',
         ],
    ]) ?>

    <?= Module::t('', "This {startLink}order{endLink} has already been paid!", [
        'dot' => true,
        'startLink' => Html::beginTag('a', ['href' => Url::to(['default/invoice', 'alias' => $model->alias])]),
        'endLink' => Html::endTag('a'),
    ]); ?>

    <?php Alert::end(); ?>

<?php } else {?>
    <?php $form = ActiveForm::begin([
        'id' => 'returnform',
        'action' => Yii::$app->oplata->url,
    ]);?>
        <?php
            foreach ($request as $name => $value) {
                echo Html::hiddenInput($name, $value);
            }
        ?>
    <?php ActiveForm::end(); ?>

<?php }?>
