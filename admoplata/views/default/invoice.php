<?php

use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model OplataTransaction */

$admoplata = Module::getInstance();

$admoplata->layout = $admoplata->invoiceLayout;
$this->title = Yii::t('adm/admoplata',"Invoice: #{id}, {title}", ['id' => $model->id, 'title' => $model->title, 'dot' => false]);
?>
<div class="admoplata-invoice admoplata-container">

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6">

                    <h5 class="lg-title mb10"><?= Yii::t('adm/admoplata','From', ['dot' => true]) ?></h5>
                    <address>
                        <?= Yii::t('adm/admoplata',"<strong>Web Services, Inc.</strong>\n795 Folsom Ave, Suite 600\n<abbr>P:</abbr> (123) 000-000", ['dot' => true]) ?>
                    </address>

                </div><!-- col-sm-6 -->

                <div class="col-sm-6 text-right">
                    <h4 class="text-primary"><?= Yii::t('adm/admoplata','Invoice No. {invoice-number}', ['invoice-number' => $model->id,'dot' => true]) ?></h4>
                    <div>
                        <?php if ($model->user_id) {?>
                            <?= Yii::t('adm/admoplata',"To: {email}\nInvoice Date: {date}\nStatus: {status}\n{description}", [
                                'email' => $model->email,
                                'date' => Yii::$app->formatter->asDate($model->created_at),
                                'time' => Yii::$app->formatter->asTime($model->created_at),
                                'status' => $model::status_list($model->response_status),
                                'description' => nl2br($model->description),
                                'dot' => false,
                            ]); ?>
                        <?php } else {?>
                            <?= Yii::t('adm/admoplata',"To: {person}\nEmail: {email}\nInvoice Date: {date}\nStatus: {status}\n{description}", [
                                'person' => $model->person,
                                'email' => $model->email,
                                'date' => Yii::$app->formatter->asDate($model->created_at),
                                'time' => Yii::$app->formatter->asTime($model->created_at),
                                'status' => $model::status_list($model->response_status),
                                'description' => nl2br($model->description),
                                'dot' => false,
                            ]); ?>
                        <?php }?>

                        <?= Yii::t('adm/admoplata',"To: {email}\nInvoice Date: {date}\nStatus: {status}\n{description}", [
                            'dot' => '.',
                        ]); ?>
                        <?= Yii::t('adm/admoplata',"To: {person}\nEmail: {email}\nInvoice Date: {date}\nStatus: {status}\n{description}", [
                            'dot' => '.',
                        ]); ?>
                    </div>
                </div>
            </div><!-- row -->

            <div class="table-responsive">
                <table class="table table-bordered table-dark table-invoice">
                    <thead>
                        <tr>
                            <th><?= Yii::t('adm/admoplata','Item', ['dot' => true]) ?></th>
                            <th><?= Yii::t('adm/admoplata','Quantity', ['dot' => true]) ?></th>
                            <th><?= Yii::t('adm/admoplata','Unit Price', ['dot' => true]) ?></th>
                            <th><?= Yii::t('adm/admoplata','Total Price', ['dot' => true]) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($model->items as $item) {
                            $sum = $item->price * $item->amount;
                            $total += $sum;
                        ?>
                        <tr>
                            <td class="admoplata-invoice-item">
                                <div><?= $item->title ?></div>
                                <?php if ($item->description) {?>
                                    <p><?= $item->description ?></p>
                                <?php }?>
                            </td>
                            <td class="admoplata-invoice-amount"><?= $item->amount ?></td>
                            <td class="admoplata-invoice-price"><?= Yii::$app->oplata->price($item->price, $model->currency); ?></td>
                            <td class="admoplata-invoice-sum"><?= Yii::$app->oplata->price($sum, $model->currency); ?></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div><!-- table-responsive -->

            <table class="table table-total">
                <tbody>
                <tr>
                    <td><?= Yii::t('adm/admoplata','Sub Total:', ['dot' => true]) ?></td>
                    <td><?= Yii::$app->oplata->price($total, $model->currency); ?></td>
                </tr>
                <tr>
                    <td><?= Yii::t('adm/admoplata','Shipping:', ['dot' => true]) ?></td>
                    <td><?= Yii::$app->oplata->price($model->shipping, $model->currency); ?></td>
                </tr>
                <tr>
                    <td><?= Yii::t('adm/admoplata','TOTAL:', ['dot' => true]) ?></td>
                    <td><?= Yii::$app->oplata->price($total + $model->shipping, $model->currency); ?></td>
                </tr>
                </tbody>
            </table>

            <div class="text-right btn-invoice clearfix">
                <?php if ($model->response_status === OplataTransaction::STATUS_NOT_PAID) {?>
                <a href="<?= Url::to(['send', 'alias' => $model->alias]) ?>" class="btn btn-primary btn-lg mr5"><i class="fa fa-dollar mr5"></i> <?= Yii::t('adm/admoplata','Make A Payment', ['dot' => false]) ?></a>
                    <div class="mb30"></div>
                <?php }?>
                <?= Yii::t('adm/admoplata','Make A Payment', ['dot' => '.']) ?>

            </div>

            <div class="alert alert-info nomargin">
                <?= Yii::t('adm/admoplata','Thank you for your business. Please make sure all cheques payable to <strong>Web Services, Inc.</strong>', ['dot' => true]) ?>
            </div>


        </div><!-- panel-body -->
    </div>

</div>
