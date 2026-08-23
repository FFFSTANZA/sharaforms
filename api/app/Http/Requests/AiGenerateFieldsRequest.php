<?php

namespace App\Http\Requests;

use App\Service\Billing\Feature;
use App\Service\Billing\PlanAccessService;
use Illuminate\Foundation\Http\FormRequest;

class AiGenerateFieldsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = $this->user();
        $hasExtendedAllowance = $user && app(PlanAccessService::class)->userHasFeature($user, Feature::AI_FORM_GENERATION);
        $maxLength = $hasExtendedAllowance ? 10000 : 4000;

        return [
            'fields_prompt' => 'required|string|max:' . $maxLength,
            'current_form_structure' => 'nullable|array|max:10',
            'current_form_structure.title' => 'nullable|string|max:500',
            'current_form_structure.properties' => 'nullable|array|max:500',
            'current_form_structure.properties.*.name' => 'nullable|string|max:500',
            'current_form_structure.properties.*.type' => 'nullable|string|max:50',
            'generation_params' => 'nullable|array|max:10',
            'generation_params.presentation_style' => 'nullable|in:classic,focused,spotlight',
        ];
    }
}
