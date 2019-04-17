<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\db\Migration;

class m190417_125515__update_data__form2_form_send extends Migration
{

    public function safeUp()
    {
        $this->execute(<<<SQL
update form2_form_send f
left join cms_site s on
    f.site_code = s.code
set f.cms_site_id = s.id
SQL
        );

    }

    public function safeDown()
    {
        echo "m190417_125515__update_data__form2_form_send cannot be reverted.\n";
        return false;
    }
}