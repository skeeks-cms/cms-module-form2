<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170701_185515__alter_table__form2_form_send_property extends Migration
{

    public function safeUp()
    {
        $this->addColumn("{{%form2_form_send_property}}", "value_num2", $this->decimal(18, 4));
        $this->createIndex("form2_form_send_property__value_num2", "{{%form2_form_send_property}}", "value_num2");

        $this->addColumn("{{%form2_form_send_property}}", "value_int2", $this->integer());
        $this->createIndex("form2_form_send_property__value_int2", "{{%form2_form_send_property}}", "value_int2");

        $this->addColumn("{{%form2_form_send_property}}", "value_string", $this->string(255));
        $this->createIndex("form2_form_send_property__value_string", "{{%form2_form_send_property}}", "value_string");

    }

    public function safeDown()
    {
        echo "m170701_173515__alter_table__form2_form_send_property cannot be reverted.\n";
        return false;
    }
}