<?php

namespace App\Rules;

use App\Support\Recaptcha as RecaptchaVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * "I'm not a robot" check for a form field named g-recaptcha-response.
 * Fails closed: if Google cannot be reached the form is rejected, unless
 * $allowWhenUnavailable is set (used for login so a Google outage cannot lock admins out).
 */
class Recaptcha implements ValidationRule
{
    public bool $implicit = true;

    public function __construct(private bool $allowWhenUnavailable = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! RecaptchaVerifier::enabled()) {
            return;
        }
        $result = RecaptchaVerifier::verify(is_string($value) ? $value : null, request()->ip());
        if ($result === RecaptchaVerifier::PASSED || ($result === RecaptchaVerifier::UNAVAILABLE && $this->allowWhenUnavailable)) {
            return;
        }
        $fail($result === RecaptchaVerifier::UNAVAILABLE
            ? 'We could not check that you are human right now. Please try again in a moment.'
            : 'Please tick “I’m not a robot” before continuing.');
    }
}
