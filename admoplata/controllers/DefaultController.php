<?php

/**
 * @package yii2-adm-oplata
 * @author Pavels Radajevs <pavlinter@gmail.com>
 * @copyright Copyright &copy; Pavels Radajevs <pavlinter@gmail.com>, 2015
 * @version 1.0.0
 */

namespace pavlinter\admoplata\controllers;

use pavlinter\admoplata\components\Oplata;
use pavlinter\admoplata\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'server-paypal' => ['post'],
                    'server' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @param $alias
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInvoice($alias)
    {
        if ($alias === '') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['alias' => $alias])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->render('invoice',[
            'model' => $model,
        ]);
    }

    /**
     * @param $alias
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPdf($alias)
    {
        Yii::$app->response->format = 'adm-pdf';

        if ($alias === '') {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['alias' => $alias])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $this->layout = false;

        $logo = null;
        if (isset(Module::getInstance()->pdf['image']['src'])) {
            $logo = Html::img(Module::getInstance()->pdf['image']['src'], Module::getInstance()->pdf['image']);
            if (isset(Module::getInstance()->pdf['imageLink']['href'])) {
                $logo = Html::a($logo, Module::getInstance()->pdf['imageLink']['href']);
            }
        }

        return $this->render('pdf',[
            'model' => $model,
            'logo' => $logo,
        ]);
    }

    /**
     * @param $alias
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSend($alias)
    {
        if (!$alias) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where(['alias' => $alias])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        
        if ($model->order_status !== null) {
            return $this->redirect(['invoice', 'alias' => $model->alias]);
        }

        $languages = Yii::$app->getI18n()->getLanguages();
        if (isset($languages[$model->language_id])) {
            $lang = $languages[$model->language_id][Yii::$app->getI18n()->langColCode];
            if (!in_array($lang, ['ru', 'uk', 'en', 'lv'])) {
                $lang = 'en';
            }
        } else {
            $lang = 'en';
        }

        $request = array(
            'lang' => $lang,
            'order_id' => $model->id . Oplata::ORDER_SEPARATOR . time(),
            'order_desc' => $model->title,
            'currency' => $model->currency,
            'amount' => ($model->price + $model->shipping) * 100,
            'merchant_id' => Yii::$app->oplata->merchantId,
            'response_url' => Url::to(['response'], true),
            'server_callback_url' => Url::to(['server'], true),
        );

        if ($model->order_status === null) {
            $signature = Yii::$app->oplata->getSignature($request);
            $request['signature'] = $signature;
            $isPaid = false;
        } else {
            $isPaid = true;
        }

        return $this->render('send',[
            'model' => $model,
            'request' => $request,
            'isPaid' => $isPaid,
        ]);
    }


    /**
     * Client side
     * @param null $alias
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionResponse($alias = null)
    {

        if ($alias) {
            $where = ['alias' => $alias];
        } else {
            list($order_id,) = explode(Oplata::ORDER_SEPARATOR, Yii::$app->request->post('order_id'));
            $where = ['id' => $order_id];
        }

        $model = Module::getInstance()->manager->createOplataTransactionQuery()->where($where)->one();

        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('response',[
            'model' => $model,
        ]);
    }

    /**
     * Server response
     */
    public function actionServer()
    {
        Yii::$app->oplata->checkPayment(Yii::$app->request->post());
        $errors = Yii::$app->oplata->getErrors();
        foreach ($errors as $error) {
            Yii::error($error, 'admoplata');
        }

    }

    /**
     * @param $alias
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSendPaypal($alias)
    {
        $module = Module::getInstance();
        if (!$alias) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = $module->manager->createOplataTransactionQuery()->where(['alias' => $alias])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->order_status !== null || !$model->paypalHasCurrency()) {
            return $this->redirect(['invoice', 'alias' => $model->alias]);
        }

        $params = http_build_query([
            'cmd' => '_xclick',
            'business' => Yii::$app->oplata->paypalBusinessId,
            'currency_code' => $model->currency,
            'return' => Url::to(['response', 'alias' => $model->alias], true),
            'cancel_return' => Url::to(['invoice', 'alias' => $model->alias]),
            'cbt' => Module::t('paypal', 'Back to Site', ['dot' => false]),
            'custom' => $model->id,
            'quantity' => 1,
            'lc' => strtoupper(Yii::$app->language),
            'item_name' => $model->title,
            'amount' => $model->price + $model->shipping,
        ]);

        $redirect = 'https://www.paypal.com/cgi-bin/webscr?' . $params;

        return $this->redirect($redirect);
        //Redirect("?cmd=_xclick&business=xxxxxxxxx&currency_code=".$currency_code."&return=".$url."&cancel_ return=".$cancel_url."&cbt=".$merchant_button."&custom=".$login."&quantity=1&lc=LV&item_name=".$item_name."&amount=".$price);
    }

    /**
     * Server response
     */
    public function actionServerPaypal()
    {
        Yii::$app->oplata->checkPaymentPaypal(Yii::$app->request->post());
        $errors = Yii::$app->oplata->getErrors();
        foreach ($errors as $error) {
            Yii::error($error, 'admoplata');
        }

    }
}
