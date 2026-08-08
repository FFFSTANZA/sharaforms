<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiGenerateFieldsRequest;
use App\Http\Requests\AiGenerateFormRequest;
use App\Models\Forms\AI\AiFormCompletion;
use App\Service\AI\AiProviderManager;
use Illuminate\Http\Request;

class AiFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:ai-generate')->only(['generateForm', 'generateFields']);
    }

    public function generateForm(AiGenerateFormRequest $request)
    {
        if (! AiProviderManager::hasAvailableProvider()) {
            return $this->error(['message' => 'AI form generation is not configured. Please contact support.'], 503);
        }

        return $this->success([
            'message' => 'We\'re working on your form, please wait ~1 min.',
            'ai_form_completion_id' => AiFormCompletion::create([
                'user_id' => $request->user()?->id,
                'form_prompt' => $request->input('form_prompt'),
                'generation_params' => $request->input('generation_params', []),
                'ip' => $request->ip(),
            ])->id,
        ]);
    }

    public function show(Request $request, AiFormCompletion $aiFormCompletion)
    {
        if ($aiFormCompletion->user_id !== null) {
            if ($aiFormCompletion->user_id !== $request->user()->id) {
                return $this->error(['message' => 'You are not authorized to view this AI completion.'], 403);
            }
        } elseif ($aiFormCompletion->ip !== $request->ip()) {
            // Legacy rows created before user binding: fall back to IP match.
            return $this->error(['message' => 'You are not authorized to view this AI completion.'], 403);
        }

        return $this->success([
            'message' => 'Your data is ready! Feel free to customize it to your needs before publishing.',
            'ai_form_completion' => [
                'id' => $aiFormCompletion->id,
                'status' => $aiFormCompletion->status,
                'result' => $aiFormCompletion->result,
            ],
        ]);
    }

    public function generateFields(AiGenerateFieldsRequest $request)
    {
        if (! AiProviderManager::hasAvailableProvider()) {
            return $this->error(['message' => 'AI form generation is not configured. Please contact support.'], 503);
        }

        return $this->success([
            'message' => 'Generating your fields, please wait...',
            'ai_form_completion_id' => AiFormCompletion::create([
                'user_id' => $request->user()?->id,
                'type' => AiFormCompletion::TYPE_FIELDS,
                'form_prompt' => $request->input('fields_prompt'),
                'context' => $request->input('current_form_structure'),
                'generation_params' => $request->input('generation_params', []),
                'ip' => $request->ip(),
            ])->id,
        ]);
    }
}