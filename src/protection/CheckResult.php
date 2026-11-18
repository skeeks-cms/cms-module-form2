<?php

namespace skeeks\modules\cms\form2\protection;

/**
 * The result of the checks of one submission: reasons of suspicion, details and the one-time key of the stamp.
 */
class CheckResult
{
    /** @var string[] codes of the reasons, see FormProtection::reasonLabels() */
    public $reasons = [];

    /** @var array details for the submission card (no personal data) */
    public $data = [];

    /** @var string|null the one-time key of a valid signed stamp */
    public $submitKey;

    public function addReason(string $code): void
    {
        if (!in_array($code, $this->reasons, true)) {
            $this->reasons[] = $code;
        }
    }

    public function isSuspicious(): bool
    {
        return (bool)$this->reasons;
    }
}
