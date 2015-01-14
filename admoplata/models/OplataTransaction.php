<?php

namespace pavlinter\admoplata\models;

use pavlinter\adm\Adm;
use pavlinter\admoplata\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%oplata_transaction}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $person
 * @property string $email
 * @property string $title
 * @property string $description
 * @property string $payment_id
 * @property string $price
 * @property string $shipping
 * @property string $currency
 * @property string $order_status
 * @property string $response_status
 * @property string $data
 * @property string $response_data
 * @property string $alias
 * @property string $created_at
 * @property string $sent_email
 *
 *
 * @property OplataItem[] $items
 * @property User $user
 */
class OplataTransaction extends \yii\db\ActiveRecord
{
    const CURRENCY_EUR = "EUR";
    const CURRENCY_USD = "USD";
    const CURRENCY_RUB = "RUB";
    const CURRENCY_UAH = "UAH";

    const STATUS_NOT_PAID = 'not paid';
    const STATUS_FAILURE  = 'failure';
    const STATUS_SUCCESS  = 'success';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oplata_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'payment_id', 'language_id'], 'integer'],
            [['price', 'currency','response_status', 'alias', 'title', 'language_id'], 'required'],
            [['price', 'shipping'], 'double'],
            [['email'], 'email'],
            [['currency'], 'in', 'range' => array_keys(self::currency_list())],
            [['response_status'], 'in', 'range' => array_keys(self::status_list())],
            [['data', 'response_data'], 'safe'],
            [['order_status', 'response_status'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 1024],
            [['description'], 'string'],
            [['email', 'person'], 'string', 'max' => 255],
            [['alias'], 'string', 'max' => 32],
            [['sent_email'], 'boolean'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['createOrder'] = ['user_id', 'email', 'shipping', 'data', 'title',  'description', 'currency', 'language_id'];
        $scenarios['admCreate'] = ['user_id', 'email', 'person', 'title', 'description', 'shipping', 'currency', 'language_id', 'sent_email', 'response_status'];
        $scenarios['admUpdate'] = $scenarios['admCreate'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (is_array($this->data) || is_object($this->data)) {
            $this->data = serialize($this->data);
        }
        if (is_array($this->response_data) || is_object($this->response_data)) {
            $this->response_data = serialize($this->response_data);
        }
        if (in_array($this->scenario, ['admCreate', 'createOrder'])) {
            $this->alias = md5(serialize($this) . uniqid('oplata_', true));
        }
        if (in_array($this->scenario, ['admCreate', 'createOrder', 'admUpdate'])) {
            if ($this->user_id && $this->user) {
                $this->email = $this->user->email;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $data = @unserialize($this->data);
        if ($data !== false) {
            $this->data = $data;
        }
        $response_data = @unserialize($this->response_data);
        if ($response_data !== false) {
            $this->response_data = $response_data;
        }

        return parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('modelAdm/oplata_transaction', 'ID'),
            'user_id' => Yii::t('modelAdm/oplata_transaction', 'User'),
            'language_id' => Yii::t('modelAdm/oplata_transaction', 'Language'),
            'person' => Yii::t('modelAdm/oplata_transaction', 'Person'),
            'email' => Yii::t('modelAdm/oplata_transaction', 'Email'),
            'title' => Yii::t('modelAdm/oplata_transaction', 'Title'),
            'description' => Yii::t('modelAdm/oplata_transaction', 'Description'),
            'payment_id' => Yii::t('modelAdm/oplata_transaction', 'Payment'),
            'price' => Yii::t('modelAdm/oplata_transaction', 'Price'),
            'shipping' => Yii::t('modelAdm/oplata_transaction', 'Shipping'),
            'currency' => Yii::t('modelAdm/oplata_transaction', 'Currency'),
            'order_status' => Yii::t('modelAdm/oplata_transaction', 'Order Status'),
            'response_status' => Yii::t('modelAdm/oplata_transaction', 'Status'),
            'data' => Yii::t('modelAdm/oplata_transaction', 'Data'),
            'response_data' => Yii::t('modelAdm/oplata_transaction', 'Response Data'),
            'alias' => Yii::t('modelAdmoplata_transaction', 'Alias'),
            'created_at' => Yii::t('modelAdm/oplata_transaction', 'Created'),
            'sent_email' => Yii::t('modelAdm/oplata_transaction', 'Email Sent'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {

        return $this->hasMany(Module::getInstance()->manager->createOplataItemQuery('className'), ['oplata_transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Adm::getInstance()->manager->createUserQuery('className'), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Adm::getInstance()->manager->createLanguageQuery('className'), ['id' => 'language_id']);
    }

    /**
     * @param null $currency
     * @return array|null
     */
    public static function currency_list($currency = false)
    {
        $list = [
            self::CURRENCY_EUR => Yii::t('modelAdm/oplata_transaction', 'EUR'),
            self::CURRENCY_USD => Yii::t('modelAdm/oplata_transaction', 'USD'),
            self::CURRENCY_RUB => Yii::t('modelAdm/oplata_transaction', 'RUB'),
            self::CURRENCY_UAH => Yii::t('modelAdm/oplata_transaction', 'UAH'),
        ];
        if ($currency) {
            if (isset($list[$currency])) {
                return $list[$currency];
            }
            return null;
        }
        return $list;
    }

    /**
     * @param bool $status
     * @return array|null
     */
    public static function status_list($status = false)
    {
        $list = [
            self::STATUS_NOT_PAID => Yii::t('modelAdm/oplata_transaction', 'Not paid'),
            self::STATUS_FAILURE => Yii::t('modelAdm/oplata_transaction', 'Failure'),
            self::STATUS_SUCCESS => Yii::t('modelAdm/oplata_transaction', 'Success'),
        ];
        if ($status !== false) {
            if (isset($list[$status])) {
                return $list[$status];
            }
            return null;
        }
        return $list;
    }

}
