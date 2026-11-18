<?php

namespace skeeks\modules\cms\form2\protection;

use yii\base\BaseObject;

/**
 * The built-in bot check without third-party services: a small computational task for the browser.
 *
 * The server picks a secret number in [0, maxNumber], gives the browser `salt` and
 * `challenge = sha256(salt . number)` signed together with the form stamp; the browser finds the number by
 * enumeration (well under a second on a phone) and sends it with the form. The visitor sees nothing.
 *
 * It makes a mass submission by direct requests expensive and requires a working browser script; it does not stop
 * a person filling the form by hand or a fully automated real browser.
 */
class PowBotCheck extends BaseObject implements BotCheckInterface
{
    /** @var int the upper bound of the secret number: the average work of the browser is half of it */
    public $maxNumber = 50000;

    /** @var string HMAC key; FormProtection passes its own */
    public $secret = '';

    public function getType(): string
    {
        return 'pow';
    }

    public function issue(string $token): array
    {
        $salt = bin2hex(random_bytes(12)).'.';
        $number = random_int(0, max(1, (int)$this->maxNumber));
        $challenge = hash('sha256', $salt.$number);

        return [
            'type' => $this->getType(),
            'algorithm' => 'SHA-256',
            'salt' => $salt,
            'challenge' => $challenge,
            'max' => (int)$this->maxNumber,
            'signature' => $this->sign($token, $salt, $challenge),
        ];
    }

    public function verify(string $token, $answer): BotCheckResult
    {
        if (!is_array($answer)) {
            return BotCheckResult::failed('нет решения');
        }
        $salt = isset($answer['salt']) && is_string($answer['salt']) ? $answer['salt'] : '';
        $challenge = isset($answer['challenge']) && is_string($answer['challenge']) ? $answer['challenge'] : '';
        $signature = isset($answer['signature']) && is_string($answer['signature']) ? $answer['signature'] : '';
        $number = $answer['number'] ?? null;

        if ($salt === '' || $challenge === '' || $signature === '') {
            return BotCheckResult::failed('нет решения');
        }
        if (!hash_equals($this->sign($token, $salt, $challenge), $signature)) {
            return BotCheckResult::failed('чужая задача');
        }
        if (!is_int($number) && !(is_string($number) && ctype_digit($number))) {
            return BotCheckResult::failed('нет решения');
        }
        $number = (int)$number;
        if ($number < 0 || $number > (int)$this->maxNumber) {
            return BotCheckResult::failed('неверное решение');
        }
        if (!hash_equals($challenge, hash('sha256', $salt.$number))) {
            return BotCheckResult::failed('неверное решение');
        }

        return BotCheckResult::passed();
    }

    private function sign(string $token, string $salt, string $challenge): string
    {
        return hash_hmac('sha256', 'pow|'.$token.'|'.$salt.'|'.$challenge, (string)$this->secret);
    }
}
