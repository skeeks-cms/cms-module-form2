<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150307_162725_create_form_phone_table extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form_phone}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form_phone}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'value'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'form_id'                => Schema::TYPE_INTEGER . '(255) NOT NULL',
            
        ], $tableOptions);

        $this->execute("ALTER TABLE {{%form_phone}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%form_phone}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%form_phone}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%form_phone}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%form_phone}} ADD INDEX(value);");
        $this->execute("ALTER TABLE {{%form_phone}} ADD UNIQUE(form_id,value);");

        $this->execute("ALTER TABLE {{%form_phone}} COMMENT = 'Телефоны форм';");

        $this->addForeignKey(
            'form_phone_created_by', "{{%form_phone}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form_phone_updated_by', "{{%form_phone}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
        
        $this->addForeignKey(
            'form_phone_form_id', "{{%form_phone}}",
            'form_id', '{{%form_form}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey("form_phone_created_by", "{{%form_phone}}");
        $this->dropForeignKey("form_phone_updated_by", "{{%form_phone}}");
        
        $this->dropForeignKey("form_phone_form_id", "{{%form_phone}}");

        $this->dropTable("{{%form_phone}}");
    }
}
