<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Schema;
use yii\db\Migration;

class m190417_115515__alter_table__form2_form_send extends Migration
{

    public function safeUp()
    {
        $tableName = "form2_form_send";
        $this->addColumn($tableName, "cms_site_id", $this->integer(11));
        $this->createIndex($tableName.'__cms_site_id', $tableName, 'cms_site_id');
        $this->addForeignKey(
            "{$tableName}__cms_site_id", $tableName,
            'cms_site_id', '{{%cms_site}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        echo "m190417_115515__alter_table__form2_form_send cannot be reverted.\n";
        return false;
    }
}