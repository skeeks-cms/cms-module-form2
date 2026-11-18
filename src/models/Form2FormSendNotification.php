<?php

namespace skeeks\modules\cms\form2\models;

use yii\db\ActiveRecord;

/**
 * A notification of an accepted submission to one recipient and the state of its delivery.
 *
 * @property int         $id
 * @property int         $form_send_id
 * @property string      $channel    email
 * @property string      $recipient
 * @property string      $status     pending, sending, sent, failed
 * @property int         $attempts
 * @property string|null $last_error
 * @property int         $created_at
 * @property int         $updated_at
 * @property int|null    $sent_at
 */
class Form2FormSendNotification extends ActiveRecord
{
    const CHANNEL_EMAIL = 'email';

    const STATUS_PENDING = 'pending';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    /**
     * The delivery was interrupted after the letter was handed to the mailer: it may have gone. Never sent again
     * automatically — a person checks with the recipient.
     */
    const STATUS_UNKNOWN = 'unknown';

    /** A delivery that stays «sending» longer than this was interrupted: it becomes STATUS_UNKNOWN, not a retry. */
    const STALE_SENDING_SECONDS = 600;

    public static function tableName()
    {
        return '{{%form2_form_send_notification}}';
    }

    /**
     * The identity of a recipient as the unique index (form_send_id, channel, recipient) sees it: the column has a
     * case-insensitive collation, so «A@x.ru» and «a@x.ru» are one recipient.
     */
    public static function recipientKey(string $recipient): string
    {
        return mb_strtolower(trim($recipient), 'UTF-8');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает отправки',
            self::STATUS_SENDING => 'Отправляется',
            self::STATUS_SENT => 'Отправлено',
            self::STATUS_FAILED => 'Не доставлено',
            self::STATUS_UNKNOWN => 'Результат неизвестен',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string)$this->status;
    }

    /**
     * The table is complete: the unique index of a recipient (it stops duplicate letters), the index of the status
     * (delivery reads rows by it) and the foreign key to the submission — everything m261118_110000 creates. A table
     * left by an interrupted migration without any of them is not ready; a repeated run of the migration adds them.
     */
    private static function detectTable(): bool
    {
        $db = self::getDb();
        $schema = $db->getTableSchema(self::tableName(), true);
        if ($schema === null) {
            return false;
        }
        $unique = false;
        foreach ($db->getSchema()->findUniqueIndexes($schema) as $columns) {
            if ($columns === ['form_send_id', 'channel', 'recipient']) {
                $unique = true;
                break;
            }
        }
        $foreignKey = false;
        foreach ($schema->foreignKeys as $fk) {
            if (isset($fk['form_send_id']) && $fk['form_send_id'] === 'id') {
                $foreignKey = true;
                break;
            }
        }
        // An index whose first column is `status` (the migration names it form2_form_send_notification__status).
        $statusIndex = false;
        $rows = $db->createCommand('SHOW INDEX FROM '.$db->quoteTableName($schema->fullName))->queryAll();
        foreach ($rows as $row) {
            if ((int)$row['Seq_in_index'] === 1 && $row['Column_name'] === 'status') {
                $statusIndex = true;
                break;
            }
        }

        return $unique && $foreignKey && $statusIndex;
    }

    public static function isTableReady(): bool
    {
        static $ready = [];
        $db = self::getDb();
        $key = $db->dsn.'|'.$db->tablePrefix;
        if (!array_key_exists($key, $ready)) {
            try {
                $ready[$key] = self::detectTable();
            } catch (\Throwable $e) {
                $ready[$key] = false;
            }
        }

        return $ready[$key];
    }
}
