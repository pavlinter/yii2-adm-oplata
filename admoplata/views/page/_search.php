<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model app\models\PageSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="page-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'id_parent') ?>

    <?= $form->field($model, 'layout') ?>

    <?= $form->field($model, 'weight') ?>

    <?= $form->field($model, 'visible') ?>

    <?php // echo $form->field($model, 'active') ?>

    <div class="form-group">
        <?= Html::submitButton(Adm::t('admpage', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Adm::t('admpage', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
