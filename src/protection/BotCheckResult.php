<?php

namespace skeeks\modules\cms\form2\protection;

/**
 * The answer of a bot check: passed, failed, or the check could not be made (a service is unavailable).
 */
class BotCheckResult
{
    const PASSED = 'passed';
    const FAILED = 'failed';
    const UNAVAILABLE = 'unavailable';

    /** @var string */
    public $status;

    /** @var string|null a short technical note for the submission card */
    public $note;

    public function __construct(string $status, ?string $note = null)
    {
        $this->status = $status;
        $this->note = $note;
    }

    public static function passed(?string $note = null): self
    {
        return new self(self::PASSED, $note);
    }

    public static function failed(?string $note = null): self
    {
        return new self(self::FAILED, $note);
    }

    public static function unavailable(?string $note = null): self
    {
        return new self(self::UNAVAILABLE, $note);
    }
}
