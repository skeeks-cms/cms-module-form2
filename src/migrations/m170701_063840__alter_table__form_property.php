<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170701_063840__alter_table__form_property extends Migration
{
    public function safeUp()
    {
        $this->dropColumn("{{%form2_form_property}}", "multiple_cnt");
        $this->dropColumn("{{%form2_form_property}}", "with_description");
        $this->dropColumn("{{%form2_form_property}}", "searchable");
        $this->dropColumn("{{%form2_form_property}}", "filtrable");
        $this->dropColumn("{{%form2_form_property}}", "version");
        $this->dropColumn("{{%form2_form_property}}", "smart_filtrable");
    }

    public function safeDown()
    {
        echo "m170701_063840__alter_table__form_property cannot be reverted.\n";
        return false;
    }
}