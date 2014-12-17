<?php

namespace pavlinter\admoplata\models;

use Yii;

/**
 * This is the model class for table "{{%oplata_item}}".
 *
 * @property string $id
 * @property string $oplata_transaction_id
 * @property string $title
 * @property string $description
 * @property string $price
 *
 * @property OplataTransaction $oplataTransaction
 */
class OplataItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oplata_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'price'], 'required'],
            [['oplata_transaction_id'], 'integer'],
            [['description'], 'string'],
            [['price'], 'number'],
            [['title'], 'string', 'max' => 200]
        ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm/oplata_item', 'ID'),
            'oplata_transaction_id' => Yii::t('adm/oplata_item', 'Oplata Transaction ID'),
            'title' => Yii::t('adm/oplata_item', 'Title'),
            'description' => Yii::t('adm/oplata_item', 'Description'),
            'price' => Yii::t('adm/oplata_item', 'Price'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOplataTransaction()
    {
        return $this->hasOne(OplataTransaction::className(), ['id' => 'oplata_transaction_id']);
    }
}
