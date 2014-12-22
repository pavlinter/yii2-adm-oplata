<?php

use yii\db\Schema;
use yii\db\Migration;

class m141222_111442_adm_oplata_access extends Migration
{
    public function up()
    {
        $this->batchInsert('{{%auth_item}}', ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'],[
            [
                'AdmOplataRoot',
                2,
                'Main access to oplata module',
                NULL,
                NULL,
                time(),
                time(),
            ],
            [
                'AdmOplataAdmin',
                2,
                'Secondary access to oplata module',
                NULL,
                NULL,
                time(),
                time(),
            ],
            [
                'Adm-OplataCreate',
                1,
                NULL,
                NULL,
                NULL,
                time(),
                time(),
            ],
            [
                'Adm-OplataRead',
                1,
                NULL,
                NULL,
                NULL,
                time(),
                time(),
            ],
            [
                'Adm-OplataUpdate',
                1,
                NULL,
                NULL,
                NULL,
                time(),
                time(),
            ],
            [
                'Adm-OplataDelete',
                1,
                NULL,
                NULL,
                NULL,
                time(),
                time(),
            ],
            [
                'Adm-OplataDeleteItem',
                1,
                NULL,
                NULL,
                NULL,
                time(),
                time(),
            ],
        ]);

        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'],[
            [
                'AdmRoot',
                'AdmOplataRoot',
            ],
            [
                'AdmAdmin',
                'AdmOplataAdmin',
            ],
            [
                'AdmOplataRoot',
                'Adm-OplataCreate',
            ],
            [
                'AdmOplataRoot',
                'Adm-OplataRead',
            ],
            [
                'AdmOplataRoot',
                'Adm-OplataUpdate',
            ],
            [
                'AdmOplataRoot',
                'Adm-OplataDelete',
            ],
            [
                'AdmOplataRoot',
                'Adm-OplataDeleteItem',
            ],
            [
                'AdmOplataAdmin',
                'Adm-OplataCreate',
            ],
            [
                'AdmOplataAdmin',
                'Adm-OplataRead',
            ],
        ]);
    }

    public function down()
    {
        $this->delete('{{auth_item_child}}', "parent='AdmRoot' AND child='AdmOplataRoot'");
        $this->delete('{{auth_item_child}}', "parent='AdmAdmin' AND child='AdmOplataAdmin'");

        $this->delete('{{auth_item}}', "name='AdmOplataRoot'");
        $this->delete('{{auth_item}}', "name='AdmOplataAdmin'");
        $this->delete('{{auth_item}}', "name='Adm-OplataCreate'");
        $this->delete('{{auth_item}}', "name='Adm-OplataRead'");
        $this->delete('{{auth_item}}', "name='Adm-OplataUpdate'");
        $this->delete('{{auth_item}}', "name='Adm-OplataDelete'");
        $this->delete('{{auth_item}}', "name='Adm-OplataDeleteItem'");

    }
}
