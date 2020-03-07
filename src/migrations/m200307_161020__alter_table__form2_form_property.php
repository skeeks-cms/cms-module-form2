<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200307_161020__alter_table__form2_form_property extends Migration
{
    public function safeUp()
    {
        $this->update("{{%form2_form_property}}", ['is_required' => 0], ['is_required' => 'N']);
        $this->update("{{%form2_form_property}}", ['is_required' => 1], ['is_required' => 'Y']);

        $this->alterColumn("{{%form2_form_property}}", "is_required", $this->integer(1)->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        echo "m200129_095515__alter_table__cms_content cannot be reverted.\n";
        return false;
    }
}