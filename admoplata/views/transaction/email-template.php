<?php

use pavlinter\adm\Adm;
use pavlinter\admoplata\Module;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$I18n = Yii::$app->getI18n();
$language = $I18n->getLanguage();
?>

<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style type="text/css">
        .text-color{
            color: #333333;
        }
        .text-color2{
            color: #999999;
        }
        .text-color3{
            color: #f25b42;
        }
        .text-font{
            font-size: 17px;
        }
        .text-font2{
            font-size: 17px;
        }
        .text-font3{
            font-size: 24px;
        }
    </style>
</head>
<body class="text-color" style="background-color: #F2F2F2;">
<?php $this->beginBody() ?>

<?php

if (isset($enableDot)) {
    Yii::$app->i18n->enableDot();
} else {
    Yii::$app->i18n->disableDot();
}

?>
<div style="padding: 2%;">
    <div style="background-color: #ffffff;border-top: 5px solid #19AB8E;">
        <div style="padding: 20px;">
            <div class="text-font" style="margin: 20px 0px;">
            <?= Adm::t('oplata/mail', "Hi {username}({email})<br/>Invoice: #{order_id}", ['order_id' => $model->id, 'username' => $username, 'email' => $model->email,]) ?>
            </div>

            <table class="text-color2" style="width: 100%;">
                <tr>
                    <td style="width: 50%;"><?= Adm::t('oplata/mail', "From:") ?></td>
                    <td><?= Adm::t('oplata/mail', "domain.com") ?></td>
                </tr>
                <tr>
                    <td style="width: 50%;"><?= Adm::t('oplata/mail', "To:") ?></td>
                    <td><?= $model->email ?></td>
                </tr>
                <tr>
                    <td style="width: 50%;"><?= Adm::t('oplata/mail', "Price:") ?></td>
                    <td>
                        <span class="text-color3"><?= Yii::$app->oplata->price($model->price + $model->shipping) ?></span>
                        <span class="text-color"><sup><?= Module::getInstance()->manager->createOplataTransactionQuery('currency_list', $model->currency) ?></sup></span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;"><?= Adm::t('oplata/mail', "Created:") ?></td>
                    <td><?=Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                </tr>
                <tr>
                    <td style="width: 50%;"><?= Adm::t('oplata/mail', "Payment day:") ?></td>
                    <td><?=Yii::$app->formatter->asDatetime($model->date_end) ?></td>
                </tr>
                <tr>
                    <td style="width: 50%;"><?= Adm::t('oplata/mail', "Title:") ?></td>
                    <td><?= $model->title ?></td>
                </tr>
            </table>

            <div style="text-align: center; padding: 10px 0px;">
                <a class="text-font3" href="<?= Url::to(['default/invoice', 'alias' => $model->alias], true) ?>" style="background: none repeat scroll 0 0 #88ad09;color: white;display: inline-block;padding: 8px 20px;text-decoration: none;">
                    <?= Adm::t('oplata/mail', "Pay") ?>
                </a>
            </div>

            <div class="text-font" style="margin: 10px 0px;">
                <?= Adm::t('oplata/mail', "Link: {url}", ['url' => Url::to(['default/invoice', 'alias' => $model->alias], true)]) ?>
            </div>
        </div>

    </div>
</div>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

<?php
Yii::$app->i18n->resetDot();
?>