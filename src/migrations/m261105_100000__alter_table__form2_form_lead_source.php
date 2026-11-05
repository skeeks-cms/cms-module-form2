<?php

use yii\db\Migration;

/**
 * The channel of the deal a submission creates (owner's decision 05.11.2026): empty — «Форма», `visit` — «Визит в
 * салон / офис» (a form at the entrance, a QR code in the showroom). Repeatable; rollback refused while a form uses it.
 */
class m261105_100000__alter_table__form2_form_lead_source extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%form2_form}}', true);
        if ($schema && $schema->getColumn('lead_source_type') === null) {
            $this->addColumn('{{%form2_form}}', 'lead_source_type', $this->string(32)->null()->comment('Канал сделки из этой формы'));
        }

        return true;
    }

    public function safeDown()
    {
        $schema = $this->db->getTableSchema('{{%form2_form}}', true);
        if (!$schema || $schema->getColumn('lead_source_type') === null) {
            return true;
        }
        $used = (new \yii\db\Query())->from('{{%form2_form}}')->where(['not', ['lead_source_type' => null]])->count('*', $this->db);
        if ($used > 0) {
            echo "Откат невозможен: у форм ({$used}) выбран канал сделки. Снимите выбор в настройках форм и повторите откат.\n";
            return false;
        }
        $this->dropColumn('{{%form2_form}}', 'lead_source_type');

        return true;
    }
}
