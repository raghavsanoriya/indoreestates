<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha
{
    public const PASSED = 'passed';

    public const FAILED = 'failed';

    public const UNAVAILABLE = 'unavailable';

    public static function enabled(): bool
    {
        return filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret_key'));
    }

    public static function siteKey(): ?string
    {
        return config('services.recaptcha.site_key');
    }

    /** Ask Google whether a checkbox token is genuine. UNAVAILABLE means Google could not be reached or answer. */
    public static function verify(?string $token, ?string $ip): string
    {
        if (blank($token)) {
            return self::FAILED;
        }
        try {
            $result = Http::asForm()->connectTimeout(3)->timeout(5)
                ->post(config('services.recaptcha.verify_url'), ['secret' => config('services.recaptcha.secret_key'), 'response' => $token, 'remoteip' => $ip])
                ->throw()->json();
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('reCAPTCHA verification unavailable: '.$exception->getMessage());

            return self::UNAVAILABLE;
        }
        if (in_array('invalid-input-secret', $result['error-codes'] ?? [], true) || in_array('missing-input-secret', $result['error-codes'] ?? [], true)) {
            Log::error('reCAPTCHA secret key is missing or invalid.');
        }

        return ($result['success'] ?? false) === true ? self::PASSED : self::FAILED;
    }
}
