<?php

namespace App\Models\Forms\AI;

use App\Jobs\Form\GenerateAiForm;
use App\Jobs\Form\GenerateAiFormFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiFormCompletion extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const TYPE_FORM = 'form';
    public const TYPE_FIELDS = 'fields';

    protected $table = 'ai_form_completions';

    protected $fillable = [
        'user_id',
        'form_prompt',
        'status',
        'result',
        'ip',
        'error',
        'type',
        'context',
        'generation_params',
        'input_tokens',
        'output_tokens',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'type' => self::TYPE_FORM,
    ];

    protected function casts()
    {
        return [
            'context' => 'array',
            'generation_params' => 'array',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected static function booted()
    {
        // Dispatch completion job on creation
        static::created(function (self $completion) {
            if ($completion->type === self::TYPE_FORM) {
                GenerateAiForm::dispatch($completion);
            } elseif ($completion->type === self::TYPE_FIELDS) {
                GenerateAiFormFields::dispatch($completion);
            }
        });
    }
}
