<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200309_185900__drop_to_del_columns extends Migration
{
    public function safeUp()
    {
        $this->dropColumn("form2_form_property", "active__to_del");
        $this->dropColumn("form2_form_property", "multiple__to_del");
    }

    public function safeDown()
    {
        echo "m200129_095515__alter_table__cms_content cannot be reverted.\n";
        return false;
    }
}