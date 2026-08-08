<?php

use App\Models\Forms\AI\AiFormCompletion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Retain AI completion records (prompts, results, IPs) for at most 30 days.
Schedule::call(function () {
    $deleted = AiFormCompletion::query()
        ->where('created_at', '<', now()->subDays(30))
        ->where('status', '!=', AiFormCompletion::STATUS_PROCESSING)
        ->delete();

    if ($deleted > 0) {
        Log::info("Pruned {$deleted} expired AI form completions.");
    }
})->daily()->name('ai-form-completions:prune');