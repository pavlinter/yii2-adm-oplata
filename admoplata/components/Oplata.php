<?php

namespace pavlinter\admoplata\components;
use pavlinter\admoplata\Module;

/**
 * Class Oplata
 */
class Oplata extends \yii\base\Object
{
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';
    const SIGNATURE_SEPARATOR = '|';
    const ORDER_SEPARATOR = ":";

    public $merchantId;

    public $password;

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
        'sender_email'
    ];

    private $errors;
    private $response;

    public function init()
    {
        if ($this->merchantId === null) {
            $this->merchantId = Module::getInstance()->merchantId;
        }
        if ($this->password === null) {
            $this->password = Module::getInstance()->merchantPassword;
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
            $order->data = $data;
            if (!$order->save(false)) {
                return false;
            }
        }
        return true;
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