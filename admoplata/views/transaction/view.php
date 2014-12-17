<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Adm::t('oplata', 'Oplata Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oplata-transaction-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Adm::t('oplata', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Adm::t('oplata', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Adm::t('oplata', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'email:email',
            'payment_id',
            'price',
            'shipping',
            'currency',
            'order_status',
            'response_status',
            'data:ntext',
            'response_data:ntext',
            'alias',
            'created_at',
        ],
    ]) ?>

</div>
