<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200309_184000__alter_table__form2_form_send_property extends Migration
{
    public function safeUp()
    {
        $tableName = "form2_form_send_property";
        $tablePropertyName = "form2_form_property";
        $tablePropertyEnumName = "form2_form_property_enum";

        $this->addColumn($tableName, "value_enum_id", $this->integer());

        $this->createIndex("value_enum_id", $tableName, "value_enum_id");

        $this->addForeignKey(
            "{$tableName}__value_enum_id", $tableName,
            'value_enum_id', $tablePropertyEnumName, 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200129_095515__alter_table__cms_content cannot be reverted.\n";
        return false;
    }
}