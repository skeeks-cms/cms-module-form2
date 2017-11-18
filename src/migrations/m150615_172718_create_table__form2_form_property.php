<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150615_172718_create_table__form2_form_property extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form2_form_property}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form2_form_property}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'name'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'code'                  => Schema::TYPE_STRING . '(64) NULL',

            'active'                => "CHAR(1) NOT NULL DEFAULT 'Y'",
            'priority'              => "INT NOT NULL DEFAULT '500'",
            'property_type'         => "CHAR(1) NOT NULL DEFAULT 'S'",
            'list_type'             => "CHAR(1) NOT NULL DEFAULT 'L'",
            'multiple'              => "CHAR(1) NOT NULL DEFAULT 'N'",
            'multiple_cnt'          => "INT NULL",
            'with_description'      => "CHAR(1) NULL",
            'searchable'            => "CHAR(1) NOT NULL DEFAULT 'N'",
            'filtrable'             => "CHAR(1) NOT NULL DEFAULT 'N'",
            'is_required'           => "CHAR(1) NULL",
            'version'               => "INT NOT NULL DEFAULT '1'",
            'component'             => "VARCHAR(255) NULL",
            'component_settings'    => "TEXT NULL",
            'hint'                  => "VARCHAR(255) NULL",
            'smart_filtrable'       => "CHAR(1) NOT NULL DEFAULT 'N'",

            'form_id'               => Schema::TYPE_INTEGER . ' NULL',

        ], $tableOptions);

        $this->createIndex('updated_by', '{{%form2_form_property}}', 'updated_by');
        $this->createIndex('created_by', '{{%form2_form_property}}', 'created_by');
        $this->createIndex('created_at', '{{%form2_form_property}}', 'created_at');
        $this->createIndex('updated_at', '{{%form2_form_property}}', 'updated_at');

        $this->createIndex('name', '{{%form2_form_property}}', 'name');
        $this->createIndex('code', '{{%form2_form_property}}', ['code', 'form_id'], true);

        $this->createIndex('active', '{{%form2_form_property}}', 'active');
        $this->createIndex('priority', '{{%form2_form_property}}', 'priority');
        $this->createIndex('property_type', '{{%form2_form_property}}', 'property_type');
        $this->createIndex('list_type', '{{%form2_form_property}}', 'list_type');
        $this->createIndex('multiple', '{{%form2_form_property}}', 'multiple');
        $this->createIndex('multiple_cnt', '{{%form2_form_property}}', 'multiple_cnt');
        $this->createIndex('with_description', '{{%form2_form_property}}', 'with_description');
        $this->createIndex('searchable', '{{%form2_form_property}}', 'searchable');
        $this->createIndex('filtrable', '{{%form2_form_property}}', 'filtrable');
        $this->createIndex('is_required', '{{%form2_form_property}}', 'is_required');
        $this->createIndex('version', '{{%form2_form_property}}', 'version');
        $this->createIndex('component', '{{%form2_form_property}}', 'component');
        $this->createIndex('hint', '{{%form2_form_property}}', 'hint');
        $this->createIndex('smart_filtrable', '{{%form2_form_property}}', 'smart_filtrable');
        $this->createIndex('form_id', '{{%form2_form_property}}', 'form_id');


        $this->addForeignKey(
            'form2_form_property_created_by', "{{%form2_form_property}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form2_form_property_updated_by', "{{%form2_form_property}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form2_form_property_form2_form', "{{%form2_form_property}}",
            'form_id', '{{%form2_form}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        echo "m150615_172718_create_table__form2_form_property cannot be reverted.\n";
        return false;
    }
}