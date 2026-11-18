<?php

namespace skeeks\modules\cms\form2\protection;

/**
 * A way to tell a browser from a bot (the built-in task for the browser, later captcha services).
 *
 * The browser asks /form2/backend/challenge for a signed form stamp; `issue()` adds what the browser needs for this
 * check. The browser JS finds a solver by `type` (sx.Form2Protection.solvers[type]) and sends its answer with the
 * form; `verify()` checks that answer on the server. A check is bound to the one-time stamp, so an answer can not be
 * reused for another submission.
 */
interface BotCheckInterface
{
    /** The solver of the browser, the key of sx.Form2Protection.solvers. */
    public function getType(): string;

    /**
     * Data for the browser.
     *
     * @param string $token the signed one-time form stamp
     */
    public function issue(string $token): array;

    /**
     * @param string $token  the signed one-time form stamp sent with the form
     * @param mixed  $answer the answer of the browser solver
     */
    public function verify(string $token, $answer): BotCheckResult;
}
