<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150615_182718_create_table__form2_form_property_enum extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%form2_form_property_enum}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form2_form_property_enum}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'property_id'           => Schema::TYPE_INTEGER . ' NULL',

            'value'                 => Schema::TYPE_STRING . '(255) NOT NULL',
            'def'                   => "CHAR(1) NOT NULL DEFAULT 'N'",
            'code'                  => Schema::TYPE_STRING. '(32) NOT NULL',
            'priority'              => Schema::TYPE_INTEGER. "(11) NOT NULL DEFAULT '500'",

        ], $tableOptions);

        $this->createIndex('form2_form_property_enum__updated_by', '{{%form2_form_property_enum}}', 'updated_by');
        $this->createIndex('form2_form_property_enum__created_by', '{{%form2_form_property_enum}}', 'created_by');
        $this->createIndex('form2_form_property_enum__created_at', '{{%form2_form_property_enum}}', 'created_at');
        $this->createIndex('form2_form_property_enum__updated_at', '{{%form2_form_property_enum}}', 'updated_at');

        $this->createIndex('form2_form_property_enum__property_id', '{{%form2_form_property_enum}}', 'property_id');
        $this->createIndex('form2_form_property_enum__def', '{{%form2_form_property_enum}}', 'def');
        $this->createIndex('form2_form_property_enum__code', '{{%form2_form_property_enum}}', 'code');
        $this->createIndex('form2_form_property_enum__priority', '{{%form2_form_property_enum}}', 'priority');
        $this->createIndex('form2_form_property_enum__value', '{{%form2_form_property_enum}}', 'value');

        $this->addForeignKey(
            'form2_form_property_enum_created_by', "{{%form2_form_property_enum}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form2_form_property_enum_updated_by', "{{%form2_form_property_enum}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form2_form_property_enum_property_id', "{{%form2_form_property_enum}}",
            'property_id', '{{%form2_form_property}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        echo "m150615_182718_create_table__form2_form_property_enum cannot be reverted.\n";
        return false;
    }
}