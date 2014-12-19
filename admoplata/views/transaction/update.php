<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */

$this->title = Adm::t('oplata', 'Update Order: ', [
    'modelClass' => 'Oplata Transaction',
]) . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Adm::t('oplata', 'List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Adm::t('oplata', 'Update');
?>
<div class="oplata-transaction-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
    ]) ?>

</div>
