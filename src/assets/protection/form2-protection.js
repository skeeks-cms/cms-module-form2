/**
 * Protection of the forms of the form constructor (skeeks/cms-module-form2).
 *
 * On the first interaction with a form the browser takes a signed one-time stamp with a bot check task
 * (/form2/backend/challenge), solves the task in the background and puts the answer into a hidden field. If the
 * visitor sends the form before that, the sending waits for the answer (at most 15 seconds, then goes without it:
 * the server saves such a submission as suspicious instead of losing it).
 *
 * Solvers of check types: sx.Form2Protection.solvers[type](check) → Promise of the answer. Built in: `pow`.
 *
 * After a submission the event `form2Conversion` is triggered only for a new submission the server accepted —
 * use it for analytics goals instead of `ajaxFormSuccessSubmited`:
 *
 *     sx.EventManager.on('form2Conversion', function (e, data) {
 *         if (data.form_code == 'feedback') { ym(COUNTER, 'reachGoal', 'feedback'); }
 *     });
 */
(function (sx, $) {
    'use strict';

    var K = [
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
    ];

    /** SHA-256 of an ASCII string as hex (the task strings are ASCII only). */
    function sha256(ascii) {
        var H = [0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19];
        var l = ascii.length, words = [], w = new Array(64), i, j;
        for (i = 0; i < l; i++) {
            words[i >> 2] |= (ascii.charCodeAt(i) & 0xff) << ((3 - (i % 4)) * 8);
        }
        words[l >> 2] |= 0x80 << ((3 - (l % 4)) * 8);
        var n = (((l + 8) >> 6) + 1) * 16;
        words[n - 1] = l * 8;

        for (j = 0; j < n; j += 16) {
            var a = H[0], b = H[1], c = H[2], d = H[3], e = H[4], f = H[5], g = H[6], h = H[7];
            for (i = 0; i < 64; i++) {
                if (i < 16) {
                    w[i] = words[j + i] | 0;
                } else {
                    var x = w[i - 15], y = w[i - 2];
                    var s0 = ((x >>> 7) | (x << 25)) ^ ((x >>> 18) | (x << 14)) ^ (x >>> 3);
                    var s1 = ((y >>> 17) | (y << 15)) ^ ((y >>> 19) | (y << 13)) ^ (y >>> 10);
                    w[i] = (w[i - 16] + s0 + w[i - 7] + s1) | 0;
                }
                var S1 = ((e >>> 6) | (e << 26)) ^ ((e >>> 11) | (e << 21)) ^ ((e >>> 25) | (e << 7));
                var ch = (e & f) ^ (~e & g);
                var t1 = (h + S1 + ch + K[i] + w[i]) | 0;
                var S0 = ((a >>> 2) | (a << 30)) ^ ((a >>> 13) | (a << 19)) ^ ((a >>> 22) | (a << 10));
                var maj = (a & b) ^ (a & c) ^ (b & c);
                var t2 = (S0 + maj) | 0;
                h = g; g = f; f = e; e = (d + t1) | 0; d = c; c = b; b = a; a = (t1 + t2) | 0;
            }
            H[0] = (H[0] + a) | 0; H[1] = (H[1] + b) | 0; H[2] = (H[2] + c) | 0; H[3] = (H[3] + d) | 0;
            H[4] = (H[4] + e) | 0; H[5] = (H[5] + f) | 0; H[6] = (H[6] + g) | 0; H[7] = (H[7] + h) | 0;
        }

        var hex = '';
        for (i = 0; i < 8; i++) {
            hex += ('00000000' + (H[i] >>> 0).toString(16)).slice(-8);
        }
        return hex;
    }

    var Protection = sx.Form2Protection = sx.Form2Protection || {};
    Protection.sha256 = sha256;
    Protection.solvers = Protection.solvers || {};

    /** The built-in task: find the number whose hash with the salt is the challenge. */
    Protection.solvers.pow = function (check) {
        return new Promise(function (resolve) {
            var max = parseInt(check.max, 10) || 0, number = 0, chunk = 2000;
            function step() {
                var end = Math.min(number + chunk, max + 1);
                for (; number < end; number++) {
                    if (sha256(check.salt + number) === check.challenge) {
                        resolve({salt: check.salt, challenge: check.challenge, signature: check.signature, number: number});
                        return;
                    }
                }
                if (number > max) {
                    resolve(null);
                    return;
                }
                setTimeout(step, 0);
            }
            step();
        });
    };

    function encode(data) {
        try {
            return window.btoa(JSON.stringify(data));
        } catch (e) {
            return '';
        }
    }

    /**
     * Attaches the protection to one instance of a form widget: the stamp, the waiting and the reset belong to this
     * form element only.
     *
     * @param ajaxSubmit sx.classes.activeForm.AjaxSubmit created for this instance just before
     * @param opts {url, formDomId, formId, model, field, trapField, waitMs}
     */
    Protection.attach = function (ajaxSubmit, opts) {
        if (!ajaxSubmit || ajaxSubmit.id !== opts.formDomId) {
            if (window.console) {
                console.warn('Form2Protection: the ajax submit of #' + opts.formDomId + ' is not found');
            }
            return;
        }
        var form = document.getElementById(opts.formDomId);
        if (!form || form.getAttribute('data-form2-protected')) {
            return;
        }
        form.setAttribute('data-form2-protected', '1');

        // Old custom templates may still render their own trap and time fields: keep only ours.
        Array.prototype.forEach.call(form.querySelectorAll('input[name="' + opts.trapField + '"]:not([data-form2-trap]), input[name="form_time"]'), function (input) {
            input.parentNode.removeChild(input);
        });

        var waitMs = opts.waitMs || 15000;
        var state = {promise: null, fetchedAt: 0, ttl: 0, waiting: false};
        var resubmitting = false;

        function field() {
            var input = form.querySelector('input[name="' + opts.field + '"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = opts.field;
                form.appendChild(input);
            }
            return input;
        }

        function isFresh() {
            return state.ttl > 0 && (Date.now() - state.fetchedAt) < (state.ttl - 120) * 1000;
        }

        function prepare() {
            if (state.promise && (!state.ttl || isFresh())) {
                return state.promise;
            }
            state.fetchedAt = Date.now();
            state.ttl = 0;
            field().value = '';
            state.promise = new Promise(function (resolve) {
                $.ajax({url: opts.url, data: {form: opts.formId, model: opts.model}, dataType: 'json', cache: false, timeout: 10000})
                    .done(function (response) {
                        if (!response || !response.token) {
                            resolve('');
                            return;
                        }
                        state.ttl = parseInt(response.ttl, 10) || 0;
                        var check = response.check || null;
                        var solver = check && Protection.solvers[check.type];
                        Promise.resolve(solver ? solver(check) : null).then(function (answer) {
                            resolve(encode({token: response.token, check: answer}));
                        }, function () {
                            resolve(encode({token: response.token, check: null}));
                        });
                    })
                    .fail(function () {
                        resolve('');
                    });
            }).then(function (value) {
                if (value) {
                    field().value = value;
                } else {
                    state.promise = null;
                }
                return value;
            });
            return state.promise;
        }

        ['focusin', 'pointerdown', 'touchstart', 'input', 'change'].forEach(function (name) {
            form.addEventListener(name, function () {
                prepare();
            }, {passive: true});
        });

        // Runs before the jQuery handlers of the form: holds the sending until the answer is ready.
        form.addEventListener('submit', function (e) {
            if (resubmitting) {
                return;
            }
            if (field().value && isFresh()) {
                return;
            }
            e.preventDefault();
            e.stopImmediatePropagation();
            if (state.waiting) {
                return;
            }
            state.waiting = true;
            var done = false;
            var finish = function (value) {
                if (done) {
                    return;
                }
                done = true;
                state.waiting = false;
                field().value = value || '';
                resubmitting = true;
                try {
                    $(form).trigger('submit');
                } finally {
                    resubmitting = false;
                }
            };
            setTimeout(function () {
                finish(field().value);
            }, waitMs);
            prepare().then(finish);
        }, true);

        ajaxSubmit.on('success', function (e, response) {
            // The stamp is used: the next submission from this page takes a new one.
            state.promise = null;
            state.ttl = 0;
            field().value = '';

            var result = response && response.data && response.data.form2;
            if (result && result.conversion) {
                sx.EventManager.trigger('form2Conversion', {
                    form: ajaxSubmit,
                    form_code: $(form).data('form_code'),
                    send_id: result.send_id
                });
            }
        });
    };
})(sx, sx.$);
