<?php

use pavlinter\admoplata\Module;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */
/* @var $form yii\widgets\ActiveForm */


$users = Adm::getInstance()->manager->createUserQuery()->all();
?>

<div class="oplata-transaction-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map($users, 'id', function ($data) {
            return $data['username'] . ' - ' . $data['email'];
        }),
        'options' => ['placeholder' => Adm::t('','Select ...', ['dot' => false])],
        'pluginOptions' => [
            'allowClear' => true,
        ]
    ]); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'shipping')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'currency')->widget(\kartik\widgets\Select2::classname(), [
        'data' => Module::getInstance()->manager->createOplataTransactionQuery('currency_list'),
    ]); ?>



    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Adm::t('oplata', 'Create') : Adm::t('oplata', 'Update'), ['class' => 'btn btn-primary']) ?>
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
');
