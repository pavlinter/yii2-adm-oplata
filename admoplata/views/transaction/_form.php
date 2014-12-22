<?php

use pavlinter\admoplata\Module;
use pavlinter\multifields\MultiFields;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */
/* @var $form yii\widgets\ActiveForm */

$users = Adm::getInstance()->manager->createUserQuery()->all();

?>

<div class="oplata-transaction-form m-t-lg">

    <?php $form = ActiveForm::begin(['id' => 'order-form',]); ?>

    <div class="row">
        <div class="col-md-6">
            <section class="panel">
                <header class="panel-heading">
                    <?php if ($model->isNewRecord) {?>
                        <?= Adm::t('oplata', 'Order') ?>
                    <?php } else {?>
                        <span class="glyphicon glyphicon-eye-open"></span>
                        <a href="<?= Url::to(['default/invoice', 'alias' => $model->alias]) ?>" class="btn-link" target="_blank"><?= Adm::t('oplata', 'Order') ?></a>
                        <span class="text-muted m-l-sm pull-right"><i class="fa fa-clock-o"></i> <?= Yii::$app->formatter->asDatetime($model->created_at)?></span>
                    <?php }?>
                </header>
                <section class="panel-body">

                    <div class="row">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
                                'data' => \yii\helpers\ArrayHelper::map($users, 'id', function ($data) {
                                    return $data['username'] . ' - ' . $data['email'];
                                }),
                                'options' => ['placeholder' => Adm::t('','Select ...', ['dot' => false])],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ]
                            ]); ?>
                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'description')->textarea() ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <?= $form->field($model, 'price')->textInput(['readonly' => true]) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= $form->field($model, 'shipping')->textInput(['maxlength' => 7]) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= $form->field($model, 'currency')->widget(\kartik\widgets\Select2::classname(), [
                                'data' => Module::getInstance()->manager->createOplataTransactionQuery('currency_list'),
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'response_status')->widget(\kartik\widgets\Select2::classname(), [
                                'data' => Module::getInstance()->manager->createOplataTransactionQuery('status_list'),
                            ]); ?>
                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'payment_id')->textInput(['readonly' => true]) ?>
                        </div>
                    </div>
                </section>
            </section>
        </div>
        <div class="col-md-6">

            <?= MultiFields::widget([
                'models' => $items,
                'form' => $form,
                'attributes' => [
                    'title',
                    [
                        'attribute' => 'description',
                        'options'=> [],
                        'field' => function ($activeField, $options, $parentClass, $closeButtonClass) {
                            return $activeField->textArea($options);
                        },
                    ],
                    'amount',
                    'price',
                ],
                'clientOptions' => [
                    'deleteRouter' => Url::to(['delete-item']),
                    'deleteCallback' => new JsExpression('function(data,$row,$form){
                        if(data.r){
                            $row.remove();
                            if (typeof data.price !== "undefined"){
                                $("#oplatatransaction-price").val(data.price);
                            }
                        }else{
                            $row.show();
                        }
                    }'),
                    'completeDelete' => new JsExpression('function(parent,form){

                    }'),
                ],
                'templateFields' => '{title}{description}<div class="row"><div class="col-md-6">{price}</div><div class="col-md-6">{amount}</div></div>',
                'template' => function($parentOptions, $closeButtonClass, $templateFields){ //default
                    ob_start();
                    ?>
                    <section class="panel">
                        <header class="panel-heading">
                            <?= Adm::t('oplata', 'Item') ?>
                            <a href="javascript:void(0);" class="btn btn-white btn-xs <?= $closeButtonClass ?>"><i class="fa fa-trash-o text-muted"></i> <?= Adm::t('oplata', 'Remove') ?></a>
                        </header>
                        <section class="panel-body">
                            <?= $templateFields ?>
                        </section>
                    </section>
                    <?php
                    return Html::tag('div', ob_get_clean(), $parentOptions);
                },
            ]);?>

        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-xs-6">
                <?= Html::submitButton($model->isNewRecord ? Adm::t('oplata', 'Create') : Adm::t('oplata', 'Update'), ['class' => 'btn btn-primary']) ?>
            </div>
            <div class="col-xs-6">
                <a class="btn btn-s-md btn-white cloneBtn" href="javascript:void(0);">
                    <i class="fa fa-plus text"></i>
                    <span class="text"><?= Adm::t('oplata', 'Add Item') ?></span>
                </a>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

$this->registerJs('
    $("#oplatatransaction-user_id").on("select2-selecting", function(e){
        $("#oplatatransaction-email").prop("disabled",true);
    }).on("select2-removed", function(e){
        $("#oplatatransaction-email").prop("disabled",false);
    });
    if($("#oplatatransaction-user_id").select2("val")){
        $("#oplatatransaction-email").prop("disabled",true);
    }

    $("#order-form").on("beforeSubmit",function(e){
        var $form = $(this);
        jQuery.ajax({
                url: $form.attr("action"),
                type: "POST",
                dataType: "json",
                data: $form.serialize(),
                success: function(d) {
                    if(d.r) {
                        location.href = "' . Url::to(['index']) . '";
                    } else {
                        $form.trigger("updateErrors",[d.errors]).trigger("scrollToError");
                    }
                },
            });
        return false;
    });

    $(".cloneBtn").on("afterAppend.mf", function(e,clone,settings){
        $("html, body").animate({
            scrollTop: $("." + settings.parentClass + ":last").offset().top
        }, 1000);
    });
');
