<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \pavlinter\admpages\models\Page */
/* @var $request array */

$this->registerJs('
    $("#returnform").submit();
');

?>
<form id="returnform" action="<?= Yii::$app->oplata->url ?>" method="post">
    <?php
        foreach ($request as $name => $value) {
            echo Html::hiddenInput($name, $value);
        }
    ?>
</form>
