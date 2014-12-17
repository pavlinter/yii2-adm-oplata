<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */

$this->title = Adm::t('oplata', 'Create {modelClass}', [
    'modelClass' => 'Oplata Transaction',
]);
$this->params['breadcrumbs'][] = ['label' => Adm::t('oplata', 'Oplata Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oplata-transaction-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
