<?php

use kartik\checkbox\CheckboxX;
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
/* @var $userModel \pavlinter\adm\models\User */

$userModel = Adm::getInstance()->manager->createUser();

$languages = Yii::$app->getI18n()->getLanguages();

$attributes = $userModel->attributes();
$var = '';
foreach ($attributes as $attribute) {
    if (in_array($attribute, ['auth_key', 'password_hash', 'password_reset_token', 'role', 'status'])) {
        continue;
    }
    $var .= "{" . $attribute . "} - " . $userModel->getAttributeLabel($attribute) . "<br/>";
}

?>

<div class="oplata-transaction-form m-t-lg">

    <?php $form = Adm::begin('ActiveForm', ['id' => 'order-form']); ?>

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
                            <?= $form->field($model, 'title')->textInput() ?>
                        </div>

                        <div class="col-sm-6">
                            <?php

                            $url = Url::to(['user-list']);
                            $initScript = <<< SCRIPT
                            function (element, callback) {
                                var id=\$(element).val();
                                if (id !== "") {
                                    \$.ajax("{$url}?id=" + id, {
                                        dataType: "json"
                                    }).done(function(data) { callback(data.results);});
                                }
                            }
SCRIPT;
                            echo $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
                                'options' => ['placeholder' => Adm::t('','Select ...', ['dot' => false])],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 3,
                                    'ajax' => [
                                        'url' => $url,
                                        'dataType' => 'json',
                                        'data' => new JsExpression('function(term,page) { return {search:term}; }'),
                                        'results' => new JsExpression('function(data,page) {
                                            return {results:data.results}; }
                                        '),
                                    ],
                                    'escapeMarkup' => new JsExpression('function (m) { return m; }'),
                                    'initSelection' => new JsExpression($initScript)
                                ],
                                'pluginEvents' => [
                                    'change' => 'function(e) {
                                        if(e.added){
                                            $("#oplatatransaction-description").val(e.added.template);
                                        }
                                    }',
                                ],
                            ]); ?>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'person')->textInput(['maxlength' => 255]) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'description',[
                                'template' => "<a href=\"javascript:void(0);\" data-toggle=\"popover\" data-content=\"" . $var . "\" data-placement=\"top\" data-html=\"true\" class=\"fa fa-cog\"></a> {label} " . Adm::t('oplata', "Email - {email} Username - {username}", ['dot' => '.', 'dotRedirect' => 0]) . "\n{input}\n{hint}\n{error}",
                            ])->textarea() ?>
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
                        <div class="col-sm-3">
                            <?= $form->field($model, 'response_status')->widget(\kartik\widgets\Select2::classname(), [
                                'data' => Module::getInstance()->manager->createOplataTransactionQuery('status_list'),
                            ]); ?>
                        </div>
                        <div class="col-sm-3">
                            <?= $form->field($model, 'payment_id')->textInput(['readonly' => true]) ?>
                        </div>

                        <div class="col-sm-3">
                            <?= $form->field($model, 'language_id')->widget(\kartik\widgets\Select2::classname(), [
                                'data' => \yii\helpers\ArrayHelper::map($languages, 'id', 'name'),
                            ]); ?>
                        </div>

                        <div class="col-sm-3 form-without-label">

                            <?php if (!$model->isNewRecord) {?>
                                <?= $form->field($model, 'sent_email', ["template" => "{input}\n{label}\n{hint}\n{error}"])->widget(CheckboxX::classname(), ['pluginOptions'=>['threeState' => false]]); ?>
                            <?php }?>
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
                ],
                'templateFields' => '{title}{description}<div class="row"><div class="col-md-6">{price}</div><div class="col-md-6">{amount}</div></div>',
                'template' => function($parentOptions, $closeButtonClass, $templateFields){ //default
                    ob_start();
                    ?>
                    <section class="panel">
                        <header class="panel-heading">
                            <?= Adm::t('oplata', 'Item') ?>
                            <a href="javascript:void(0);" class="btn btn-white btn-xs <?= $closeButtonClass ?>"><i class="fa fa-trash-o text-muted"></i> <?= Adm::t('oplata', 'Remove', ['dot' => false]) ?></a>
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
                <?= Html::submitButton($model->isNewRecord ? Adm::t('oplata', 'Create', ['dot' => false]) : Adm::t('oplata', 'Update', ['dot' => false]), ['class' => 'btn btn-primary btnAct btnSimple']) ?>
                <?= Html::submitButton($model->isNewRecord ? Adm::t('oplata', 'Create And Send', ['dot' => false]) : Adm::t('oplata', 'Update And Send', ['dot' => false]), ['class' => 'btn btn-primary btnAct btnSend']) ?>
            </div>
            <div class="col-xs-6">
                <a class="btn btn-s-md btn-white cloneBtn" href="javascript:void(0);">
                    <i class="fa fa-plus text"></i>
                    <span class="text"><?= Adm::t('oplata', 'Add Item', ['dot' => false]) ?></span>
                </a>
            </div>
        </div>
    </div>

    <?php Adm::end('ActiveForm'); ?>

</div>


<?php

$this->registerJs('
    var disabledUpdate = ' . (!$model->isNewRecord && $model->order_status !== null ? 'true' : 'false') . ';
    if(disabledUpdate){
        $("#order-form").find(":input").prop("readonly",true);
        $(".mf-btn-close,.cloneBtn,.btnAct").hide();
        $("#oplatatransaction-sent_email").checkboxX("refresh");
    }

    $("#oplatatransaction-user_id").on("select2-selecting", function(e){
        $("#oplatatransaction-email,#oplatatransaction-person").prop("disabled",true).val("");
    }).on("select2-removed", function(e){
        $("#oplatatransaction-email,#oplatatransaction-person").prop("disabled",false);
    });
    if($("#oplatatransaction-user_id").select2("val")){
        $("#oplatatransaction-email,#oplatatransaction-person").prop("disabled",true).val("");
    }

    if(!disabledUpdate){
        var redirect;
        $(".btnSend").on("click",function(e){
            redirect = "' . Url::to(['send-email']) . '";
        });

        $(".btnSimple").on("click",function(e){
            redirect = false;
        });

        $("#order-form").on("beforeSubmit",function(e){
            var $form = $(this);
            jQuery.ajax({
                    url: $form.attr("action"),
                    type: "POST",
                    dataType: "json",
                    data: $form.serialize(),
                    success: function(d) {
                        if(d.r) {
                            if(redirect){
                                location.href = redirect + "?id=" + d.id;
                            } else {
                                location.href = "' . Url::to(['index']) . '";
                            }
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

    }
');
