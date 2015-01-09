<?php

namespace pavlinter\admoplata\components;

use pavlinter\admoplata\models\OplataTransaction;
use pavlinter\admoplata\Module;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Oplata
 */
class Oplata extends Component
{
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';
    const SIGNATURE_SEPARATOR = '|';
    const ORDER_SEPARATOR = ":";

    public $merchantId;

    public $password;

    public $url = 'https://api.oplata.com/api/checkout/redirect/'; //'https://api.oplata.com/api/checkout/url/';

    public $responseFields = [
        'rrn',
        'masked_card',
        'sender_cell_phone',
        'response_status',
        'currency',
        'fee',
        'reversal_amount',
        'settlement_amount',
        'actual_amount',
        'order_status',
        'response_description',
        'order_time',
        'actual_currency',
        'order_id',
        'tran_type',
        'eci',
        'settlement_date',
        'payment_system',
        'approval_code',
        'merchant_id',
        'settlement_currency',
        'payment_id',
        'sender_account',
        'card_bin',
        'response_code',
        'card_type',
        'amount',
        'sender_email',
    ];

    private $items = [];
    private $errors;
    private $response;

    public function init()
    {
        $this->responseFields[] = Yii::$app->request->csrfParam;

        if ($this->merchantId === null) {
            throw new InvalidConfigException('The "merchantId" property must be set.');
        }
        if ($this->password === null) {
            throw new InvalidConfigException('The "password" property must be set.');
        }
    }


    /**
     * @param $data
     * @param $password
     * @param bool $encoded
     * @return string
     */
    public function getSignature($data, $encoded = true)
    {
        $data = array_filter($data, function($var) {
            return $var !== '' && $var !== null;
        });
        ksort($data);

        $str = $this->password;
        foreach ($data as $k => $v) {
            $str .= self::SIGNATURE_SEPARATOR . $v;
        }

        if ($encoded) {
            return sha1($str);
        } else {
            return $str;
        }
    }

    /**
     * @param $response
     * @return bool|string
     */
    public function checkPayment($response)
    {
        $this->clearErrors();
        $data = serialize($response);

        $responseSignature = $response['signature'];
        foreach ($response as $k => $v) {
            if (!in_array($k, $this->responseFields)) {
                unset($response[$k]);
            }
        }
        $this->response = $response;

        if (isset($response['order_id'])) {
            list($order_id,) = explode(self::ORDER_SEPARATOR, $response['order_id']);
        } else {
            $order_id = null;
        }
        if (!$order_id) {
            $this->setError('An error has occurred during payment. Please contact us to ensure your order has submitted.');
            return false;
        }

        $order = Module::getInstance()->manager->createOplataTransactionQuery()->where(['id' => $order_id])->one();

        if (!$order) {
            $this->setError('An error has occurred during payment. Please contact us to ensure your order has submitted.');
            return false;
        }

        if ($this->merchantId != $response['merchant_id']) {
            $this->setError('An error has occurred during payment. Merchant data is incorrect.');
            return false;
        }


        if ($this->getSignature($response) != $responseSignature) {
            $this->setError('An error has occurred during payment. Signature is not valid.');
            return false;
        }

        if ($response['order_status'] == self::ORDER_DECLINED) {
            $this->setError("Thank you for shopping with us. However, the transaction has been declined.");
            return false;
        }

        if ($response['order_status'] == self::ORDER_APPROVED) {
            $order->payment_id = $response['payment_id'];
            $order->order_status = $response['order_status'];
            $order->response_status = $response['response_status'];
            $order->response_data = $data;
            if (!$order->save(false)) {
                return false;
            }
        }
        return true;
    }


    /**
     * @param $data
     * @return bool|string
     */
    public function createOrder($data)
    {
        $this->clearErrors();
        $items = $this->getItems();

        if (empty($items)) {
            $this->setError('Basket is empty!');
            return false;
        }

        $price = 0;
        foreach ($items as $item) {
            /* @var $item \pavlinter\admoplata\models\OplataItem */
            if (!$item->validate()) {
                $this->setError(reset($item->getErrors()));
                return false;
            }
            $price += $item->price * $item->amount;
        }

        $order = Module::getInstance()->manager->createOplataTransaction();
        /* @var $order \pavlinter\admoplata\models\OplataTransaction */
        $order->setScenario('createOrder');
        if ($order->load($data, '') && $order->validate()) {
            $order->price = $price;
            $order->response_status = OplataTransaction::STATUS_NOT_PAID;
            $order->save(false);

            foreach ($items as $item) {
                $order->link('items', $item);
            }
            return $order->id;
        }
        $this->setError(reset($order->getErrors()));
        return false;
    }

    /**
     * @param $int
     * @param $currency
     * @return string
     */
    public function price($int, $currency)
    {
        if ($currency) {
            $currency = ' ' . Module::getInstance()->manager->createOplataTransactionQuery('currency_list', $currency);
        } else {
            $currency = null;
        }
        return Yii::$app->formatter->asDecimal($int, 2) . $currency;
    }

    /**
     * @param $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }
    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     *
     */
    public function clearItems()
    {
        $this->items = [];
    }

    /**
     *
     */
    public function clearErrors()
    {
        $this->errors = [];
    }

    /**
     * @param $msg
     */
    public function setError($msg)
    {
        $this->errors[] = $msg;
    }
    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        $errors = $this->getErrors();
        if (!empty($errors)) {
            return reset($errors);
        }
        return null;
    }


    /**
     * @return bool
     */
    public function hasErrors()
    {
        $errors = $this->getErrors();
        return !empty($errors);
    }

    /**
     * @return bool
     */
    public function getResponse()
    {
        $this->response;
    }

}