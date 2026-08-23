<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Appends three high-intent FAQ entries to every template's question set:
 * a "how to create" slot, a "is it free" slot, and one category-specific
 * slot keyed on the template's primary type (with an interpolated fallback
 * rotation for rare types).
 *
 * Existing curated questions are never modified or removed. Composition is
 * deterministic per slug and additions are skipped when the normalized
 * question text already exists, so this seeder is safe to re-run at any time.
 */
class TemplateQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $path = resource_path('data/forms/templates/expansion-questions.json');

        if (! File::exists($path)) {
            $this->command?->error("Expansion questions bank not found at: {$path}");

            return;
        }

        $bank = json_decode(File::get($path), true);

        if (! is_array($bank) || empty($bank['universal'])) {
            $this->command?->error('Expansion questions bank is invalid JSON.');

            return;
        }

        $added = 0;
        $skipped = 0;

        Template::query()->orderBy('id')->chunk(50, function ($templates) use ($bank, &$added, &$skipped) {
            foreach ($templates as $template) {
                $subject = $this->subjectName((string) $template->name);
                $article = $this->articleFor($subject);
                $additions = [];

                foreach ($bank['universal'] as $slot) {
                    $additions[] = $this->interpolate($slot, $subject, $article);
                }
                $additions[] = $this->categorySlot($template, $bank, $subject, $article);

                $knownQuestions = collect($template->questions ?? [])
                    ->pluck('question')
                    ->map(fn ($question) => $this->normalizeQuestion((string) $question))
                    ->filter()
                    ->all();

                $toAdd = [];
                foreach ($additions as $entry) {
                    $key = $this->normalizeQuestion($entry['question']);
                    if ($key === '' || in_array($key, $knownQuestions, true)) {
                        continue;
                    }
                    $knownQuestions[] = $key;
                    $toAdd[] = [
                        'question' => $entry['question'],
                        'answer' => $entry['answer'],
                    ];
                }

                if (empty($toAdd)) {
                    $skipped++;

                    continue;
                }

                $template->questions = array_merge($template->questions ?? [], $toAdd);
                $template->save();
                $added += count($toAdd);
            }
        });

        $this->command?->info("TemplateQuestionsSeeder: appended {$added} FAQ entries across templates ({$skipped} already up to date).");
    }

    /**
     * Pick the third slot: a category-specific Q&A for the template's primary
     * type, or a stable rotation entry interpolated with the subject name so
     * sibling pages are never textually identical.
     */
    private function categorySlot(Template $template, array $bank, string $subject, string $article): array
    {
        $primaryType = is_array($template->types) ? ($template->types[0] ?? null) : null;

        if ($primaryType && isset($bank['categories'][$primaryType])) {
            return $this->interpolate($bank['categories'][$primaryType], $subject, $article);
        }

        $rotation = $bank['_rotation'] ?? [];
        if (empty($rotation)) {
            // Bank misconfiguration should never silently drop the slot.
            return $this->interpolate(
                $bank['universal']['free'],
                $subject,
                $article,
            );
        }

        $index = abs(crc32($template->slug)) % count($rotation);

        return $this->interpolate($rotation[$index], $subject, $article);
    }

    private function interpolate(array $entry, string $subject, string $article): array
    {
        return [
            'question' => str_replace(['{name}', '{article}'], [$subject, $article], (string) $entry['question']),
            'answer' => str_replace(['{name}', '{article}'], [$subject, $article], (string) $entry['answer']),
        ];
    }

    /**
     * "Donation Form Template" -> "donation form";
     * "NPS Survey Template" -> "NPS survey" (acronyms preserved).
     */
    private function subjectName(string $name): string
    {
        $subject = mb_strtolower(trim($name));
        $subject = preg_replace('/\s*form\s+template$/u', '', $subject) ?? $subject;
        $subject = preg_replace('/\s*template$/u', '', $subject) ?? $subject;
        $subject = trim(preg_replace('/\s+/u', ' ', $subject) ?? $subject);

        // Restore acronyms that lowercasing mangled (word-boundary safe).
        $subject = preg_replace_callback(
            '/\b(nps|rsvp|rfp|pto|kyc)\b/',
            fn ($matches) => strtoupper($matches[1]),
            $subject,
        ) ?? $subject;

        // Questions read naturally only when the subject ends in its noun.
        if (! preg_match('/(form|survey|quiz|poll|questionnaire|checklist|waiver)$/iu', $subject)) {
            $subject .= ' form';
        }

        return $subject;
    }

    /**
     * "an NPS survey", "a donation form": acronyms take vowel-sound letters,
     * plain words take the classic vowel rule.
     */
    private function articleFor(string $subject): string
    {
        $firstWord = strtok($subject, ' ') ?: $subject;
        $firstLetter = mb_strtolower(mb_substr($firstWord, 0, 1));

        $isAcronym = ctype_upper($firstWord) && mb_strlen($firstWord) <= 5;
        if ($isAcronym && in_array($firstLetter, ['a', 'e', 'f', 'h', 'i', 'l', 'm', 'n', 'o', 'r', 's', 'x'], true)) {
            return 'an';
        }

        return in_array($firstLetter, ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a';
    }

    private function normalizeQuestion(string $question): string
    {
        $plain = strip_tags($question);
        $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? '';

        return mb_strtolower($plain);
    }
}
