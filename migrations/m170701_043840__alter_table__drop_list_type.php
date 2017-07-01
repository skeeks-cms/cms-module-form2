<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170701_043840__alter_table__drop_list_type extends Migration
{
    public function safeUp()
    {
        $this->dropColumn("{{%form2_form_property}}", "list_type");
    }

    public function safeDown()
    {
        echo "m170622_043840__alter_table__drop_list_type cannot be reverted.\n";
        return false;
    }
}