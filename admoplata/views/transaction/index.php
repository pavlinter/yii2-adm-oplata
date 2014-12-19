<?php

use kartik\grid\GridView;
use pavlinter\admoplata\Module;
use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $searchModel app\models\OplataTransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Adm::t('oplata', 'Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oplata-transaction-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Adm::t('oplata', 'Create Order'), ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= Adm::widget('GridView',[
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '70px',
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
                    return Yii::$app->oplata->price($model->price + $model->shipping, $model->currency);
                },
            ],

            [
                'attribute' => 'response_status',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'filterType' => GridView::FILTER_SELECT2,
                'filter'=> Module::getInstance()->manager->createOplataTransactionQuery('status_list'),
                'value' => function ($model) {
                    if (!empty($model->response_status)) {
                        return Module::getInstance()->manager->createOplataTransactionQuery('status_list', $model->response_status);
                    }
                },
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' =>true ],
                ],
                'filterInputOptions' => ['placeholder' => Adm::t('','Select ...', ['dot' => false])],
                'format' => 'raw'
            ],
            [
                'attribute' => 'created_at',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'datetime',
            ],
            [
                'class' => '\kartik\grid\ActionColumn',
                'width' => '130px',
                'buttons' => [
                    'view' => function ($url, $model) {
                        if ($model->alias) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['default/invoice', 'alias' => $model->alias], [
                                'title' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ]);
                        }
                    },
                ],
            ],
        ],
    ]); ?>

</div>
