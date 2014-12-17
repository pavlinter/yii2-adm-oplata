<?php

use yii\helpers\Html;
use pavlinter\adm\Adm;

/* @var $this yii\web\View */
/* @var $model app\models\Page */

$this->title = Adm::t('admpage', 'Create {modelClass}', [
    'modelClass' => 'Page',
]);
$this->params['breadcrumbs'][] = ['label' => Adm::t('admpage', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
