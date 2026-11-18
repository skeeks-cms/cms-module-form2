<?php

use yii\db\Migration;

/**
 * Protection of public forms (task CRM 6390): the result of the checks of a submission and its diagnostic facts.
 *
 * - check_status: null — an old submission (accepted), `accepted`, `suspicious` (saved, no notification, no deal);
 * - check_reasons: codes of the reasons, comma separated; check_data: details of the checks (JSON);
 * - submit_key: the one-time key of the signed form stamp, unique — a repeated request does not make a second
 *   submission;
 * - phone: the normalized main phone, session_hash: a hash of the session id, client_id: the Metrika ClientID,
 *   user_agent — for limits and diagnostics instead of full request dumps;
 * - ip widened to 45 characters (IPv6).
 *
 * Repeatable. Old full dumps (data_server, data_cookie, …) are kept as they are.
 */
class m261118_100000__alter_table__form2_form_send_protection extends Migration
{
    private $table = '{{%form2_form_send}}';

    public function safeUp()
    {
        $schema = $this->db->getTableSchema($this->table, true);
        if (!$schema) {
            return true;
        }

        $columns = [
            'check_status' => $this->string(16)->null()->comment('Результат проверки: accepted, suspicious'),
            'check_reasons' => $this->string(255)->null()->comment('Коды причин подозрения'),
            'check_data' => $this->text()->null()->comment('Подробности проверок (JSON)'),
            'submit_key' => $this->string(64)->null()->comment('Одноразовый ключ отправки'),
            'phone' => $this->string(20)->null()->comment('Основной телефон, нормализованный'),
            'session_hash' => $this->string(64)->null()->comment('Хеш сессии посетителя'),
            'client_id' => $this->string(64)->null()->comment('ClientID Яндекс Метрики'),
            'user_agent' => $this->string(500)->null()->comment('Браузер посетителя'),
        ];
        foreach ($columns as $name => $type) {
            if ($schema->getColumn($name) === null) {
                $this->addColumn($this->table, $name, $type);
            }
        }

        $ip = $schema->getColumn('ip');
        if ($ip && (int)$ip->size < 45) {
            $this->alterColumn($this->table, 'ip', $this->string(45)->null());
        }

        $existing = $this->indexNames();
        if (!in_array('form2_form_send__submit_key', $existing, true)) {
            $this->createIndex('form2_form_send__submit_key', $this->table, 'submit_key', true);
        }
        foreach ([
            'form2_form_send__check_status' => 'check_status',
            'form2_form_send__phone' => 'phone',
            'form2_form_send__session_hash' => 'session_hash',
        ] as $index => $column) {
            if (!in_array($index, $existing, true)) {
                $this->createIndex($index, $this->table, $column);
            }
        }

        return true;
    }

    public function safeDown()
    {
        $schema = $this->db->getTableSchema($this->table, true);
        if (!$schema) {
            return true;
        }
        $existing = $this->indexNames();
        foreach (['form2_form_send__submit_key', 'form2_form_send__check_status', 'form2_form_send__phone', 'form2_form_send__session_hash'] as $index) {
            if (in_array($index, $existing, true)) {
                $this->dropIndex($index, $this->table);
            }
        }
        foreach (['check_status', 'check_reasons', 'check_data', 'submit_key', 'phone', 'session_hash', 'client_id', 'user_agent'] as $name) {
            if ($schema->getColumn($name) !== null) {
                $this->dropColumn($this->table, $name);
            }
        }
        // ip stays 45 characters: shortening it could cut stored IPv6 addresses.

        return true;
    }

    private function indexNames(): array
    {
        $rows = $this->db->createCommand('SHOW INDEX FROM '.$this->db->quoteTableName($this->db->schema->getRawTableName($this->table)))->queryAll();

        return array_values(array_unique(array_column($rows, 'Key_name')));
    }
}
