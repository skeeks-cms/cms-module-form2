<?php

use yii\db\Migration;

/**
 * Delivery of notifications of an accepted submission, one row per recipient (task CRM 6390).
 *
 * Rows are created in the transaction that accepts a submission, so an accepted submission always has its pending
 * notifications; delivery happens after the commit. A failed recipient is retried alone, a delivered one is never
 * sent again.
 *
 * Resumable: MySQL DDL is not transactional, so a run interrupted after CREATE TABLE leaves a table without its
 * constraints. A repeated run keeps the table and its rows and adds what is missing — the unique index of a
 * recipient, the status index, the foreign key. Form2FormSendNotification::isTableReady() requires all three.
 */
class m261118_110000__create_table__form2_form_send_notification extends Migration
{
    private $table = '{{%form2_form_send_notification}}';

    public function safeUp()
    {
        if ($this->db->getTableSchema($this->table, true) === null) {
            $this->createTable($this->table, [
                'id' => $this->primaryKey(),
                'form_send_id' => $this->integer()->notNull(),
                'channel' => $this->string(16)->notNull()->defaultValue('email'),
                'recipient' => $this->string(255)->notNull(),
                'status' => $this->string(16)->notNull()->defaultValue('pending')->comment('pending, sending, sent, failed, unknown'),
                'attempts' => $this->integer()->notNull()->defaultValue(0),
                'last_error' => $this->string(500)->null(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
                'sent_at' => $this->integer()->null(),
            ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }

        $indexes = $this->indexNames();
        if (!in_array('form2_form_send_notification__recipient', $indexes, true)) {
            $this->createIndex('form2_form_send_notification__recipient', $this->table, ['form_send_id', 'channel', 'recipient'], true);
        }
        if (!in_array('form2_form_send_notification__status', $indexes, true)) {
            $this->createIndex('form2_form_send_notification__status', $this->table, 'status');
        }
        $schema = $this->db->getTableSchema($this->table, true);
        if (!isset($schema->foreignKeys['form2_form_send_notification__form_send_id'])) {
            $this->addForeignKey('form2_form_send_notification__form_send_id', $this->table, 'form_send_id',
                '{{%form2_form_send}}', 'id', 'CASCADE', 'CASCADE');
        }

        return true;
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema($this->table, true) !== null) {
            $this->dropTable($this->table);
        }

        return true;
    }

    private function indexNames(): array
    {
        $rows = $this->db->createCommand('SHOW INDEX FROM '.$this->db->quoteTableName($this->db->schema->getRawTableName($this->table)))->queryAll();

        return array_values(array_unique(array_column($rows, 'Key_name')));
    }
}
