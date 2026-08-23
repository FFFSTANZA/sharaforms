<?php

namespace App\Service\AI\Prompts\Form;

class PresentationRules
{
    public const MODE_CLASSIC = 'classic';
    public const MODE_FOCUSED = 'focused';
    public const MODE_SPOTLIGHT = 'spotlight';

    /**
     * A compact example of the exact field JSON the app expects. Models that
     * cannot use schema enforcement (Gemini/Groq) follow this literal format,
     * which the FormSchemaNormalizer then validates/enforces.
     */
    public static function buildFieldContract(): string
    {
        return <<<'EOD'
        Output must be an object shaped EXACTLY like this. Keys are fixed — do not rename them (no "label", "help_text", "submit_text", "title"):
        {
          "title": "Customer Feedback",
          "re_fillable": false,
          "use_captcha": false,
          "redirect_url": null,
          "submitted_text": "<p>Thank you for your feedback!</p>",
          "uppercase_labels": false,
          "submit_button_text": "Submit",
          "re_fill_button_text": "Fill Again",
          "color": "#3B82F6",
          "properties": [
            {
              "type": "nf-text",
              "name": "Intro",
              "help": "",
              "placeholder": "",
              "hidden": false,
              "required": false,
              "width": "full",
              "content": "<h1>Customer Feedback</h1><p>Tell us how we did. It takes less than a minute.</p>"
            },
            {
              "type": "text",
              "name": "Full name",
              "help": "So we can address you personally.",
              "placeholder": "Jane Doe",
              "hidden": false,
              "required": true,
              "width": "1/2",
              "multi_lines": false,
              "generates_uuid": false,
              "max_char_limit": 500,
              "hide_field_name": false,
              "show_char_limit": false
            },
            {
              "type": "select",
              "name": "How did you hear about us?",
              "help": "",
              "placeholder": "",
              "hidden": false,
              "required": true,
              "width": "full",
              "without_dropdown": true,
              "select": { "options": [ { "name": "Search engine", "id": "search-engine" }, { "name": "Social media", "id": "social-media" }, { "name": "Word of mouth", "id": "word-of-mouth" } ] }
            },
            {
              "type": "checkbox",
              "name": "Subscribe to newsletter",
              "help": "",
              "placeholder": "",
              "hidden": false,
              "required": false,
              "width": "full",
              "use_toggle_switch": false
            }
          ]
        }
        EOD;
    }

    /**
     * Layout & design guidance that mirrors what a careful form designer
     * would do manually (width pairing, sectioning, pacing).
     */
    public static function buildLayoutGuidance(string $mode = self::MODE_CLASSIC): string
    {
        $rules = [
            'Always open with a welcoming nf-text block (h1 + a one-line intro), never with a bare question.',
            'Group related questions under section headers (nf-text with <h2>), and keep every group visually connected.',
            'Pair logically-related short fields on one row: use width "1/2" for both (e.g. first name + last name, city + postal code, month + year, day + month). Do not place unrelated full-width fields between two halves.',
            'Use width "1/2" only in pairs, "1/3" or "2/3" only in matching sets, and "full" for anything long or important.',
            'Keep each page to 4-7 blocks: split long forms with nf-page-break (never inside a section mid-flow; break between sections).',
            'Every select/multi_select needs 3-7 concrete, realistic options; prefer without_dropdown: true when 5 or fewer options, otherwise a dropdown.',
            'Use the most specific input type for the data: email → email, phone → phone_number, date → date, Yes/No → checkbox, 1-N scale → rating, options related to each other → matrix.',
            'Required fields only when the flow truly needs them; never mark optional questions required.',
            'Keep copy short and human: labels are questions, help text is a gentle hint (<= 140 chars), placeholders show an example answer.',
            'Finish with a closing nf-text block and a warm submitted_text.',
            'Always end the last block before submit with a text (nf-text) thank-you or next-steps note if it completes the flow naturally.',
        ];

        if ($mode === self::MODE_FOCUSED) {
            $guidanceMode = [
                'Focused mode presents ONE question per screen. Skip intro-screens overload: still open with a compact nf-text (title + one line), then each question as its own block.',
                'Never use width options — every field is full width.',
                'Prefer quick inputs: select over long text when choices are limited, rating for satisfaction, checkbox/toggle for yes-no.',
            ];
        } elseif ($mode === self::MODE_SPOTLIGHT) {
            $guidanceMode = [
                'Spotlight mode shows all questions on one page, but only one is active at a time. The active question is prominently displayed; answered questions collapse into compact summaries; upcoming questions appear dimmed in the background.',
                'Never use width options — every field is full width.',
                'Keep labels short and scannable since they will be visible in the dimmed state.',
                'Prefer quick inputs: select over long text when choices are limited, rating for satisfaction, checkbox/toggle for yes-no.',
            ];
        } else {
            $guidanceMode = [];
        }

        return implode("\n", [...$rules, ...$guidanceMode]);
    }

    public static function buildContext(array $params = []): array
    {
        $mode = $params['presentation_style'] ?? self::MODE_CLASSIC;

        if ($mode === self::MODE_FOCUSED) {
            return [
                'mode' => self::MODE_FOCUSED,
                'allowedFieldTypes' => [
                    'text',
                    'rich_text',
                    'date',
                    'url',
                    'phone_number',
                    'email',
                    'checkbox',
                    'select',
                    'multi_select',
                    'matrix',
                    'number',
                    'rating',
                    'scale',
                    'slider',
                    'files',
                    'signature',
                    'barcode',
                    'nf-text'
                ],
                'constraintsText' => implode("\n", [
                    'Focused mode (Typeform-like):',
                    '- Display one question per page/step, sequentially.',
                    '- Do not use widths (all inputs are full width).',
                    '- Do not use page breaks (nf-page-break).',
                    '- Do not include image (nf-image) or code (nf-code) blocks.',
                    '- Keep copy concise and engaging for each step.'
                ])
            ];
        }

        if ($mode === self::MODE_SPOTLIGHT) {
            return [
                'mode' => self::MODE_SPOTLIGHT,
                'allowedFieldTypes' => [
                    'text',
                    'rich_text',
                    'date',
                    'url',
                    'phone_number',
                    'email',
                    'checkbox',
                    'select',
                    'multi_select',
                    'matrix',
                    'number',
                    'rating',
                    'scale',
                    'slider',
                    'files',
                    'signature',
                    'barcode',
                    'nf-text'
                ],
                'constraintsText' => implode("\n", [
                    'Spotlight mode (all questions visible, one active):',
                    '- Display all questions on one page, with one active/spotlit at a time.',
                    '- Do not use widths (all inputs are full width).',
                    '- Do not use page breaks (nf-page-break).',
                    '- Do not include image (nf-image) or code (nf-code) blocks.',
                    '- Keep labels short and scannable for the dimmed overview state.'
                ])
            ];
        }

        return [
            'mode' => self::MODE_CLASSIC,
            'allowedFieldTypes' => [
                'text',
                'rich_text',
                'date',
                'url',
                'phone_number',
                'email',
                'checkbox',
                'select',
                'multi_select',
                'matrix',
                'number',
                'rating',
                'scale',
                'slider',
                'files',
                'signature',
                'barcode',
                'nf-text',
                'nf-page-break',
                'nf-divider',
                'nf-image',
                'nf-code'
            ],
            'constraintsText' => 'Classic mode: you can use layout widths and optional page breaks.'
        ];
    }
}
