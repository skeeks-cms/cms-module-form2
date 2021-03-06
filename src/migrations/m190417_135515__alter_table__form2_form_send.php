<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m190417_135515__alter_table__form2_form_send extends Migration
{

    public function safeUp()
    {
        $tableName = "form2_form_send";
        $this->dropForeignKey("form2_form_send_site_code_fk", $tableName);
        $this->dropColumn($tableName, "site_code");
    }

    public function safeDown()
    {
        echo "m190417_135515__alter_table__form2_form_send cannot be reverted.\n";
        return false;
    }
}