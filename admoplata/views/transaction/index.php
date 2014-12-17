<?php

use kartik\grid\GridView;
use pavlinter\admoplata\Module;
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
            [
                'attribute' => 'id',
                'vAlign' => 'middle',
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'payment_id',
                'vAlign' => 'middle',
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'email',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!$model->user_id) {
                        return Yii::$app->formatter->asEmail($model->email);
                    }
                },
            ],
            [
                'attribute' => 'price',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'value' => function ($model) {
                    $currency = Module::getInstance()->manager->createOplataTransactionQuery('currency_list', $model->currency);
                    return Yii::$app->formatter->asDecimal($model->price + $model->shipping, 2) . ' ' . $currency;
                },
            ],
            [
                'attribute' => 'created_at',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'datetime',
                'value' => function ($model) {
                    return $model->created_at;
                },
            ],
            [
                'attribute' => 'response_status',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'filterType' => GridView::FILTER_SELECT2,
                'filter'=> Module::getInstance()->manager->createOplataTransactionQuery('status_list'),
                'value' => function ($model) {
                    if (empty($model->response_status)) {

                    } else {
                        return Module::getInstance()->manager->createOplataTransactionQuery('status_list', $model->response_status);
                    }
                },
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' =>true ],
                ],
                'filterInputOptions' => ['placeholder' => Adm::t('','Select ...', ['dot' => false])],
                'format' => 'raw'
            ],
            // 'order_status',
            // 'response_status',

            [
                'class' => '\kartik\grid\ActionColumn',
                'width' => '130px',
            ],
        ],
    ]); ?>

</div>
