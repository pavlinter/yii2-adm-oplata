<?php
use pavlinter\admoplata\Module;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \pavlinter\admpages\models\Page */
/* @var $request array */

$admoplata = Module::getInstance();

$admoplata->layout = $admoplata->invoiceLayout;

$this->registerJs('
    $("#returnform").submit();
');

?>

<?php $form = ActiveForm::begin([
    'id' => 'returnform',
    'action' => Yii::$app->oplata->url,
]);?>
    <?php
        foreach ($request as $name => $value) {
            echo Html::hiddenInput($name, $value);
        }
    ?>
<?php ActiveForm::end(); ?>
