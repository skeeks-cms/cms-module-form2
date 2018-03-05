<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m170701_133520__alter_table__form2_form_send_property extends Migration
{
    public function safeUp()
    {
        $this->delete("{{%form2_form_send_property}}", [
            'or',
            ['element_id' => null],
            ['property_id' => null],
        ]);

        $this->dropForeignKey('form2_form_send_property_element_id', '{{%form2_form_send_property}}');
        $this->dropForeignKey('form2_form_send_property_property_id', '{{%form2_form_send_property}}');

        if ($this->db->driverName == 'mysql') {
            $this->alterColumn("{{%form2_form_send_property}}", 'element_id', $this->integer()->notNull());
            $this->alterColumn("{{%form2_form_send_property}}", 'property_id', $this->integer()->notNull());
        } else {
            $this->alterColumn("{{%form2_form_send_property}}", 'element_id', $this->integer());
            $this->alterColumn("{{%form2_form_send_property}}", 'element_id', "SET NOT NULL");
            $this->alterColumn("{{%form2_form_send_property}}", 'property_id', $this->integer());
            $this->alterColumn("{{%form2_form_send_property}}", 'property_id', "SET NOT NULL");
        }

        $this->addForeignKey(
            'form2_form_send_property_element_id', "{{%form2_form_send_property}}",
            'element_id', '{{%form2_form_send}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'form2_form_send_property_property_id', "{{%form2_form_send_property}}",
            'property_id', '{{%form2_form_property}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m170701_133520__alter_table__form2_form_send_property cannot be reverted.\n";
        return false;
    }
}