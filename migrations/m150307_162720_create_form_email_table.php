<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150307_162720_create_form_email_table extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form_email}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form_email}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'value'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'form_id'                => Schema::TYPE_INTEGER . '(255) NOT NULL',

        ], $tableOptions);

        $this->execute("ALTER TABLE {{%form_email}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%form_email}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%form_email}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%form_email}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%form_email}} ADD INDEX(value);");
        $this->execute("ALTER TABLE {{%form_email}} ADD UNIQUE(form_id,value);");

        $this->execute("ALTER TABLE {{%form_email}} COMMENT = 'Email адреса форм';");

        $this->addForeignKey(
            'form_email_created_by', "{{%form_email}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form_email_updated_by', "{{%form_email}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form_email_form_id', "{{%form_email}}",
            'form_id', '{{%form_form}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey("form_email_created_by", "{{%form_email}}");
        $this->dropForeignKey("form_email_updated_by", "{{%form_email}}");

        $this->dropForeignKey("form_email_form_id", "{{%form_email}}");

        $this->dropTable("{{%form_email}}");
    }
}
