<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model app\models\Page */

$this->title = Adm::t('admpage', 'Update {modelClass}: ', [
    'modelClass' => 'Page',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Adm::t('admpage', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Adm::t('admpage', 'Update');
?>
<div class="page-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
