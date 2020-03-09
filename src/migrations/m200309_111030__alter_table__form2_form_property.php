<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200309_111030__alter_table__form2_form_property extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%form2_form_property}}", "cms_measure_code", $this->string(20));

        $this->addForeignKey(
            "form2_form_property__measure_code", "{{%form2_form_property}}",
            'cms_measure_code', '{{%cms_measure}}', 'code', 'RESTRICT', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200309_111030__alter_table__form2_form_property cannot be reverted.\n";
        return false;
    }
}