<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model app\models\OplataTransactionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="oplata-transaction-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'email') ?>

    <?= $form->field($model, 'payment_id') ?>

    <?= $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'shipping') ?>

    <?php // echo $form->field($model, 'currency') ?>

    <?php // echo $form->field($model, 'order_status') ?>

    <?php // echo $form->field($model, 'response_status') ?>

    <?php // echo $form->field($model, 'data') ?>

    <?php // echo $form->field($model, 'response_data') ?>

    <?php // echo $form->field($model, 'alias') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Adm::t('oplata', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Adm::t('oplata', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
