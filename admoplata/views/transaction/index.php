<?php

use kartik\grid\GridView;
use pavlinter\admoplata\Module;
use yii\helpers\Html;
use pavlinter\adm\Adm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\OplataTransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

Yii::$app->i18n->disableDot();
$this->title = Adm::t('oplata', 'Orders');
$this->params['breadcrumbs'][] = $this->title;
Yii::$app->i18n->resetDot();
?>
<div class="oplata-transaction-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Adm::t('oplata', 'Create Order', ['dot' => true]), ['create'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Adm::t('oplata', 'Email Template', ['dot' => true]), ['mail'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Yii::$app->i18n->disableDot();?>

    <?= Adm::widget('GridView',[
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showPageSummary' => true,
        'columns' => [
            [
                'attribute' => 'id',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '80px',
            ],
            [
                'attribute' => 'payment_id',
                'width' => '130px',
                'vAlign' => 'middle',
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'email',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->user_id) {
                        $res = Html::a('', ['/adm/user/update', 'id' => $model->user_id], [
                            'class' => 'glyphicon glyphicon-eye-open',
                        ]) . ' ';
                        $res .= Yii::$app->formatter->asEmail($model->email);
                        return $res;
                    }
                    return Yii::$app->formatter->asEmail($model->email);
                },
            ],
            [
                'attribute' => 'price',
                'width' => '130px',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'pageSummary' => function ($summary, $data, $widget) {
                    return Adm::t('oplata', 'Count is {summary}', ['summary' => $summary,'dot' => true]);
                },
                'format' => ['decimal', 2],
                'value' => function ($model) {
                    return $model->price + $model->shipping;
                },
            ],
            [
                'attribute' => 'currency',
                'width' => '50px',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'filterType' => GridView::FILTER_SELECT2,
                'filter'=> Module::getInstance()->manager->createOplataTransactionQuery('currency_list'),
                'value' => function ($model) {
                    if (!empty($model->currency)) {
                        return Module::getInstance()->manager->createOplataTransactionQuery('currency_list', $model->currency);
                    }
                },
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true ],
                ],
                'filterInputOptions' => ['placeholder' => Adm::t('','Select ...', ['dot' => false])],
                'format' => 'raw'
            ],
            [
                'attribute' => 'response_status',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'filterType' => GridView::FILTER_SELECT2,
                'filter'=> Module::getInstance()->manager->createOplataTransactionQuery('status_list'),
                'value' => function ($model) {
                    if (!empty($model->response_status)) {
                        $text = Module::getInstance()->manager->createOplataTransactionQuery('status_list', $model->response_status);
                        $class = '';
                        if ($model->response_status === $model::STATUS_SUCCESS) {
                            $class = 'text-success';
                        } else if($model->response_status === $model::STATUS_FAILURE){
                            $class = 'text-danger';
                        }
                        return Html::tag('span', $text, [
                            'class' => $class,
                        ]);
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
                'width' => '160px',
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ],
                'vAlign' => 'middle',
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'date_end',
                'width' => '160px',
                'filterType' => GridView::FILTER_DATE,
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ],
                'vAlign' => 'middle',
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'sent_email',
                'width' => '120px',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function ($model) {
                    $remind = '';
                    if ($model->remind_note) {
                        $remind = '&nbsp;' . Html::tag('span', '', [
                            'class' => 'fa fa-envelope-o text-success cursor-help',
                            'data-toggle' => 'tooltip',
                            'title' => Adm::t('admoplata', 'Remind note sent'),
                        ]);
                    }

                    if ($model->sent_email || $model->response_status !== $model::STATUS_NOT_PAID) {
                        return Html::tag('span', '', [
                            'class' => 'glyphicon glyphicon-ok text-success cursor-help',
                            'data-toggle' => 'tooltip',
                            'title' => Adm::t('admoplata', 'Email Sent'),
                        ]) . $remind;
                    }


                    return \pavlinter\buttons\AjaxButton::widget([
                        'label' => Adm::t('oplata', 'Send'),
                        'options' => [
                            'class' => 'btn btn-primary',
                        ],
                        'ajaxOptions' => [
                            'url' => Url::to('send-email'),
                            'data' => [
                                'id' => $model->id,
                            ],
                            'done' => 'function(data){
                                if(data.r){
                                    $("#" + abId).next("span").removeClass("hide").end().remove();
                                }
                            }',
                        ],
                    ]) . Html::tag('span', '', [
                        'class' => 'glyphicon glyphicon-ok text-success hide cursor-help',
                        'data-toggle' => 'tooltip',
                        'title' => Adm::t('admoplata', 'Email Sent'),
                    ]) . $remind;
                },
            ],
            [
                'class' => '\kartik\grid\ActionColumn',
                'width' => '130px',
                'template' => '{pay} {view} {update} {delete}',
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
                    'update' => function ($url, $model) {
                        if (!Adm::getInstance()->user->can('Adm-OplataUpdate')) {
                            return null;
                        }
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        if (!Adm::getInstance()->user->can('Adm-OplataDelete')) {
                            return null;
                        }
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => Yii::t('yii', 'Delete'),
                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    },
                    'pay' => function ($url, $model) {
                        if ($model->alias) {
                            return Html::a(null, 'javascript:void(0);', [
                                'class' => 'fa fa-link',
                                'data-pjax' => '0',
                                'data-toggle' => 'popover',
                                'data-placement' => 'top',
                                'data-content' => Url::to(['default/invoice', 'alias' => $model->alias] , true),
                            ]);
                        }
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Yii::$app->i18n->resetDot();?>

</div>
