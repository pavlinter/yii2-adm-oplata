<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */

$this->title = Adm::t('oplata', 'Update {modelClass}: ', [
    'modelClass' => 'Oplata Transaction',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Adm::t('oplata', 'Oplata Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Adm::t('oplata', 'Update');
?>
<div class="oplata-transaction-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
