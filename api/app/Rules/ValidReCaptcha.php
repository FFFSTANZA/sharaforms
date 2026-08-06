<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\Http;

class ValidReCaptcha implements ImplicitRule
{
    public const RECAPTCHA_API_BASE = 'https://recaptchaenterprise.googleapis.com/v1';

    private $error = 'validation.invalid_captcha';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            $this->error = 'validation.complete_captcha';

            return false;
        }

        $projectId = config('services.re_captcha.project_id');
        $apiKey = config('services.re_captcha.secret_key');
        $siteKey = config('services.re_captcha.site_key');

        if (!$projectId || !$apiKey || !$siteKey) {
            return false;
        }

        $response = Http::asJson()->post(
            self::RECAPTCHA_API_BASE . "/projects/{$projectId}/assessments?key={$apiKey}",
            [
                'event' => [
                    'token' => $value,
                    'siteKey' => $siteKey,
                ],
            ]
        );

        $data = $response->json() ?? [];

        // Fail open when Google's monthly quota is exhausted (429 / RESOURCE_EXHAUSTED)
        // so legitimate users aren't locked out for the rest of the month.
        if ($response->status() === 429 || ($data['error']['status'] ?? null) === 'RESOURCE_EXHAUSTED') {
            return true;
        }

        $valid = (bool) ($data['tokenProperties']['valid'] ?? false);

        if (!$valid) {
            return false;
        }

        $threshold = (float) config('services.re_captcha.score_threshold', 0);

        if ($threshold <= 0) {
            return true;
        }

        $score = (float) ($data['riskAnalysis']['score'] ?? 0);

        return $score >= $threshold;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans($this->error);
    }
}
