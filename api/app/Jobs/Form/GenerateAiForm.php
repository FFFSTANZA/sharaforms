<?php

namespace App\Jobs\Form;

use App\Models\Forms\AI\AiFormCompletion;
use App\Service\AI\Prompts\Form\GenerateFormPrompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiForm implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 240;

    public array $backoff = [15, 60];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public AiFormCompletion $completion)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->completion->update([
            'status' => AiFormCompletion::STATUS_PROCESSING,
        ]);

        $prompt = new GenerateFormPrompt(
            $this->completion->form_prompt,
            $this->completion->generation_params ?? []
        );
        $formData = $prompt->execute();

        $this->completion->update([
            'status' => AiFormCompletion::STATUS_COMPLETED,
            'result' => $formData,
            'error' => null,
            'input_tokens' => $prompt->getCompleter()->getInputTokens(),
            'output_tokens' => $prompt->getCompleter()->getOutputTokens(),
        ]);
    }

    /**
     * Handle a job failure after all retries have been exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI form generation failed permanently.', [
            'ai_form_completion_id' => $this->completion->id,
            'exception' => $exception,
        ]);

        $this->completion->update([
            'status' => AiFormCompletion::STATUS_FAILED,
            'error' => 'AI generation failed. Please try again later.',
        ]);
    }
}