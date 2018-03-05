<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.03.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m160701_192740_alter_table__form2_form_send_property extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('form2_form_send_property__value', '{{%form2_form_send_property}}');

        if ($this->db->driverName == 'mysql') {
            $this->alterColumn('{{%form2_form_send_property}}', "value", "longtext NOT NULL");
        } else {
            $this->alterColumn('{{%form2_form_send_property}}', 'value', $this->text());
        }

    }

    public function down()
    {
        echo "m160701_192740_alter_table__form2_form_send_property cannot be reverted.\n";
        return false;
    }
}
