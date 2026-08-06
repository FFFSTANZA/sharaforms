<?php

namespace App\Console\Commands;

use App\Models\Forms\Form;
use App\Models\Integration\FormIntegration;
use App\Models\Integration\FormZapierWebhook;
use App\Models\PdfTemplate;
use Illuminate\Console\Command;

class PurgeSoftDeletedForms extends Command
{
    protected $signature = 'forms:purge-soft-deleted {--days=30 : Retain soft-deleted forms for this many days before purging}';

    protected $description = 'Permanently purge soft-deleted forms and their associated data after the retention window';

    public function handle(): int
    {
        $retentionDays = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($retentionDays);
        $purged = 0;

        Form::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->lazyById()
            ->each(function (Form $form) use (&$purged) {
                $form->submissions()->delete();
                $form->views()->delete();
                $form->statistics()->delete();

                FormZapierWebhook::withTrashed()->where('form_id', $form->id)->forceDelete();
                FormIntegration::withTrashed()->where('form_id', $form->id)->forceDelete();
                PdfTemplate::withTrashed()->where('form_id', $form->id)->forceDelete();

                $form->forceDelete();
                $purged++;
            });

        $this->info('Purged ' . $purged . ' soft-deleted forms.');

        return self::SUCCESS;
    }
}
