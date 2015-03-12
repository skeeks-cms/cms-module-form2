<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150307_162740_create_form_send_messages_table extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form_send_message}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form_send_message}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'data'                  => Schema::TYPE_TEXT . ' NULL',
            'additional_data'       => Schema::TYPE_TEXT . ' NULL',

            'form_id'               => Schema::TYPE_INTEGER . '(255) NULL',

        ], $tableOptions);

        $this->execute("ALTER TABLE {{%form_send_message}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%form_send_message}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%form_send_message}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%form_send_message}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%form_send_message}} ADD INDEX(form_id);");

        $this->execute("ALTER TABLE {{%form_send_message}} COMMENT = 'Сообщения с форм';");

        $this->addForeignKey(
            'form_send_message_created_by', "{{%form_send_message}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form_send_message_updated_by', "{{%form_send_message}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
        
        $this->addForeignKey(
            'form_send_message_form_id', "{{%form_send_message}}",
            'form_id', '{{%form_form}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function down()
    {
        $this->dropForeignKey("form_send_message_created_by", "{{%form_send_message}}");
        $this->dropForeignKey("form_send_message_updated_by", "{{%form_send_message}}");
        
        $this->dropForeignKey("form_send_message_form_id", "{{%form_send_message}}");

        $this->dropTable("{{%form_send_message}}");
    }
}
