<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StoreStripeKeysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Strip pasted whitespace before the format rules run.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'publishable_key' => trim((string) $this->input('publishable_key')),
            'secret_key' => trim((string) $this->input('secret_key')),
        ]);
    }

    public function rules(): array
    {
        return [
            'publishable_key' => [
                'required',
                'string',
                'regex:/^pk_(test|live)_[A-Za-z0-9]+$/',
            ],
            'secret_key' => [
                'required',
                'string',
                // Restricted (rk_) keys are recommended; full secret (sk_) keys are accepted.
                'regex:/^(sk|rk)_(test|live)_[A-Za-z0-9]+$/',
                'min:24',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'publishable_key.regex' => 'The publishable key must start with pk_live_ or pk_test_.',
            'secret_key.regex' => 'The secret key must be a Stripe restricted key (rk_live_...) or secret key (sk_live_...).',
            'secret_key.min' => 'That does not look like a valid Stripe secret key.',
        ];
    }
}
