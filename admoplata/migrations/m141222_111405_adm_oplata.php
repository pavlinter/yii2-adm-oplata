<?php

use yii\db\Schema;
use yii\db\Migration;

class m141222_111405_adm_oplata extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%oplata_transaction}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_INTEGER . " DEFAULT NULL",
            'language_id' => Schema::TYPE_INTEGER . "(11) NOT NULL",
            'person' => Schema::TYPE_STRING . "(255) DEFAULT NULL",
            'email' => Schema::TYPE_STRING . "(255) DEFAULT NULL",
            'title' => Schema::TYPE_STRING . "(1024) NOT NULL",
            'description' => Schema::TYPE_TEXT,
            'payment_id' => Schema::TYPE_BIGINT . " DEFAULT NULL",
            'price' => Schema::TYPE_DECIMAL . "(7,2) NOT NULL",
            'shipping' => Schema::TYPE_DECIMAL . "(7,2) NOT NULL DEFAULT '0.00'",
            'currency' => Schema::TYPE_STRING . "(3) NOT NULL",
            'method' => Schema::TYPE_STRING . "(50) DEFAULT NULL",
            'order_status' => Schema::TYPE_STRING . "(50) DEFAULT NULL",
            'response_status' => Schema::TYPE_STRING . "(50) NOT NULL",
            'data' => Schema::TYPE_TEXT,
            'response_data' => Schema::TYPE_TEXT,
            'alias' =>  Schema::TYPE_STRING . "(32) NOT NULL",
            'created_at' => Schema::TYPE_TIMESTAMP . " NOT NULL",
            'date_end' => Schema::TYPE_DATE . " NULL",
            'sent_email' => Schema::TYPE_BOOLEAN . "(1) NOT NULL DEFAULT '0'",
            'remind_note' => Schema::TYPE_BOOLEAN . "(1) NOT NULL DEFAULT '0'",
        ], $tableOptions);

        $this->createIndex('user_id', '{{%oplata_transaction}}', 'user_id');
        $this->createIndex('alias', '{{%oplata_transaction}}', 'alias');
        $this->createIndex('language_id', '{{%oplata_transaction}}', 'language_id');
        $this->addForeignKey('oplata_transaction_ibfk_1', '{{%oplata_transaction}}', 'user_id', '{{%user}}', 'id', 'SET NULL', 'SET NULL');

        $this->createTable('{{%oplata_item}}', [
            'id' => Schema::TYPE_BIGPK,
            'oplata_transaction_id' => Schema::TYPE_BIGINT . " NOT NULL",
            'title' => Schema::TYPE_STRING . "(200) NOT NULL",
            'description' => Schema::TYPE_TEXT,
            'amount' => Schema::TYPE_INTEGER . "(11) NOT NULL",
            'price' => Schema::TYPE_DECIMAL . "(7,2) NOT NULL",
        ], $tableOptions);

        $this->createIndex('oplata_transaction_id', '{{%oplata_item}}', 'oplata_transaction_id');
        $this->addForeignKey('oplata_item_ibfk_1', '{{%oplata_item}}', 'oplata_transaction_id', '{{%oplata_transaction}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%oplata_item}}');
        $this->dropTable('{{%oplata_transaction}}');
    }
}
