<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150615_162718_create_table__form2_form extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form2_form}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form2_form}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'name'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'description'           => Schema::TYPE_TEXT . ' NULL',

            'code'                  => Schema::TYPE_STRING . '(32) NULL',

            'emails'                => Schema::TYPE_TEXT . ' NULL',
            'phones'                => Schema::TYPE_TEXT . ' NULL',
            'user_ids'              => Schema::TYPE_TEXT . ' NULL',

        ], $tableOptions);

        $this->createIndex('updated_by', '{{%form2_form}}', 'updated_by');
        $this->createIndex('created_by', '{{%form2_form}}', 'created_by');
        $this->createIndex('created_at', '{{%form2_form}}', 'created_at');
        $this->createIndex('updated_at', '{{%form2_form}}', 'updated_at');
        $this->createIndex('name', '{{%form2_form}}', 'name');
        $this->createIndex('code', '{{%form2_form}}', 'code', true);

        $this->addCommentOnTable('{{%form2_form}}', 'Forms');


        $this->addForeignKey(
            'form2_form_created_by', "{{%form2_form}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form2_form_updated_by', "{{%form2_form}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function down()
    {
        $this->dropForeignKey("form2_form_created_by", "{{%form2_form}}");
        $this->dropForeignKey("form2_form_updated_by", "{{%form2_form}}");

        $this->dropTable("{{%form2_form}}");
    }
}
