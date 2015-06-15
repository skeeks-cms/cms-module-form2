<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (ÑêèêÑ)
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

        $this->execute("ALTER TABLE {{%form2_form}} ADD INDEX(updated_by);");
        $this->execute("ALTER TABLE {{%form2_form}} ADD INDEX(created_by);");

        $this->execute("ALTER TABLE {{%form2_form}} ADD INDEX(created_at);");
        $this->execute("ALTER TABLE {{%form2_form}} ADD INDEX(updated_at);");

        $this->execute("ALTER TABLE {{%form2_form}} ADD INDEX(name);");
        $this->execute("ALTER TABLE {{%form2_form}} ADD UNIQUE(code);");

        $this->execute("ALTER TABLE {{%form2_form}} COMMENT = 'Ôîðìû';");

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
