<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150307_162735_create_form_field_table extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form_field}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form_field}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'label'                 => Schema::TYPE_STRING . '(255) NULL',
            'hint'                  => Schema::TYPE_TEXT . ' NULL',

            'widget'                => Schema::TYPE_TEXT . ' NULL',
            'rules'                 => Schema::TYPE_TEXT . ' NULL',

            'priority'              => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',

            'attribute'             => Schema::TYPE_STRING . '(255) NOT NULL',
            'form_id'               => Schema::TYPE_INTEGER . '(255) NOT NULL',
            
        ], $tableOptions);

        $this->execute("ALTER TABLE {{%form_field}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%form_field}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%form_field}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%form_field}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%form_field}} ADD INDEX(label);");
        $this->execute("ALTER TABLE {{%form_field}} ADD INDEX(priority);");
        $this->execute("ALTER TABLE {{%form_field}} ADD UNIQUE(attribute,form_id);");

        $this->execute("ALTER TABLE {{%form_field}} COMMENT = 'Элементы форм';");

        $this->addForeignKey(
            'form_field_created_by', "{{%form_field}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'form_field_updated_by', "{{%form_field}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
        
        $this->addForeignKey(
            'form_field_form_id', "{{%form_field}}",
            'form_id', '{{%form_form}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey("form_field_created_by", "{{%form_field}}");
        $this->dropForeignKey("form_field_updated_by", "{{%form_field}}");
        
        $this->dropForeignKey("form_field_form_id", "{{%form_field}}");

        $this->dropTable("{{%form_field}}");
    }
}
