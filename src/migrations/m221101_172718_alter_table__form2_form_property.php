<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m221101_172718_alter_table__form2_form_property extends Migration
{
    public function safeUp()
    {
        $tableName = "form2_form_property";

        $this->addColumn($tableName, "cms_site_id", $this->integer()->notNull());

        $subQuery = $this->db->createCommand("
            UPDATE 
                `form2_form_property` as c
            SET 
                c.cms_site_id = (select cms_site.id from cms_site where cms_site.is_default = 1)
        ")->execute();
        
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function safeDown()
    {
        echo "m200309_111030__alter_table__form2_form_property cannot be reverted.\n";
        return false;
    }
}