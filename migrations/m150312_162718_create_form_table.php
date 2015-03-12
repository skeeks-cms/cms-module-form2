<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m150312_162718_create_form_table extends Migration
{
    public function up()
    {
        $tableExist = $this->db->getTableSchema("{{%form_form}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%form_form}}", [
            'id'                    => Schema::TYPE_PK,

            'created_by'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_by'            => Schema::TYPE_INTEGER . ' NULL',

            'created_at'            => Schema::TYPE_INTEGER . ' NULL',
            'updated_at'            => Schema::TYPE_INTEGER . ' NULL',

            'name'                  => Schema::TYPE_STRING . '(255) NOT NULL',
            'description'           => Schema::TYPE_TEXT . ' NULL',

            'emails'                => Schema::TYPE_TEXT . ' NULL',
            'phones'                => Schema::TYPE_TEXT . ' NULL',

            'elements'              => Schema::TYPE_TEXT . ' NULL',

        ], $tableOptions);

        $this->execute("ALTER TABLE {{%form_form}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%form_form}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%form_form}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%form_form}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%form_form}} ADD INDEX(name);");

        $this->execute("ALTER TABLE {{%form_form}} COMMENT = 'Конструктор форм';");

        $this->addForeignKey(
            'form_form_created_by', "{{%form_form}}",
            'created_by', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );

        $this->addForeignKey(
            'form_form_updated_by', "{{%form_form}}",
            'updated_by', '{{%cms_user}}', 'id', 'RESTRICT', 'RESTRICT'
        );
    }

    public function down()
    {
        $this->dropForeignKey("form_form_created_by", "{{%form_form}}");
        $this->dropForeignKey("form_form_updated_by", "{{%form_form}}");

        $this->dropTable("{{%form_form}}");
    }
}
