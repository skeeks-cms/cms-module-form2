<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m250528_111030__alter_table__form2_form extends Migration
{
    public function safeUp()
    {
        $tableName = "form2_form";

        $this->addColumn($tableName, "is_add_legal_checkbox", $this->integer(1)->notNull()->defaultValue(1));
        $this->addColumn($tableName, "legal_checkbox_template", $this->text()->null());

        $this->createIndex($tableName . "__is_add_legal_checkbox", $tableName, ["is_add_legal_checkbox"]);
    }

    public function safeDown()
    {
        echo "m250528_111030__alter_table__form2_form cannot be reverted.\n";
        return false;
    }
}