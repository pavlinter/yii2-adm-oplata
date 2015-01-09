<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model pavlinter\admoplata\models\OplataTransaction */

Yii::$app->i18n->disableDot();
$this->title = Adm::t('oplata', 'Create Order');
$this->params['breadcrumbs'][] = ['label' => Adm::t('oplata', 'List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
Yii::$app->i18n->resetDot();
?>
<div class="oplata-transaction-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
    ]) ?>

</div>
