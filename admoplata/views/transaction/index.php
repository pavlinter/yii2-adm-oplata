<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\OplataTransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Adm::t('oplata', 'Oplata Transactions');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oplata-transaction-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Adm::t('oplata', 'Create {modelClass}', [
    'modelClass' => 'Oplata Transaction',
]), ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= Adm::widget('GridView',[
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'email:email',
            'payment_id',
            'price',
            // 'shipping',
            // 'currency',
            // 'order_status',
            // 'response_status',
            // 'data:ntext',
            // 'response_data:ntext',
            // 'alias',
            // 'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
