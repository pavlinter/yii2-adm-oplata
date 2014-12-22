<?php

use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;

/* @var $this yii\web\View */
/* @var $model OplataTransaction */

$admoplata = Module::getInstance();

$admoplata->layout = $admoplata->invoiceLayout;
$this->title = Yii::t('adm/admoplata',"Invoice: #{id}", ['id' => $model->id, 'dot' => false]);
?>
<div class="admoplata-invoice">

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
                    <?php if ($model->email) {?>
                        <p><?= Yii::t('adm/admoplata',"<strong>To:</strong> {email}", ['email' => $model->email, 'dot' => true]) ?></p>
                    <?php }?>
                    <p><?= Yii::t('adm/admoplata','<strong>Invoice Date:</strong> {date}', ['date' => Yii::$app->formatter->asDate($model->created_at), 'time' => Yii::$app->formatter->asTime($model->created_at),'nl2br' => false, 'dot' => true]) ?></p>
                    <p><?= Yii::t('adm/admoplata','Status: ' . $model->response_status, ['dot' => true]) ?></p>
                    <?= Yii::t('adm/admoplata','Status: ' . OplataTransaction::STATUS_NOT_PAID, ['dot' => '.']) ?>
                    <?= Yii::t('adm/admoplata','Status: ' . OplataTransaction::STATUS_SUCCESS, ['dot' => '.']) ?>
                    <?= Yii::t('adm/admoplata','Status: ' . OplataTransaction::STATUS_FAILURE, ['dot' => '.']) ?>
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
                            <td>
                                <p><?= $item->description ?></p>
                            </td>
                            <td><?= $item->amount ?></td>
                            <td><?= Yii::$app->oplata->price($item->price, $model->currency); ?></td>
                            <td><?= Yii::$app->oplata->price($sum, $model->currency); ?></td>
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
                    <button class="btn btn-primary btn-lg mr5"><i class="fa fa-dollar mr5"></i> <?= Yii::t('adm/admoplata','Make A Payment', ['dot' => false]) ?></button>
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
