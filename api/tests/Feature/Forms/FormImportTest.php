<?php


use Illuminate\Support\Facades\Http;

// ---------------------------------------------------------------------------
// Controller-level import tests
// ---------------------------------------------------------------------------

it('returns prefill data for a valid import request', function () {
    Http::fake([
        '*.typeform.com/*' => Http::response(typeformHtmlFixture(), 200),
    ]);

    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => ['url' => 'https://example.typeform.com/to/abc123'],
    ])
        ->assertSuccessful()
        ->assertJsonStructure([
            'form' => ['title', 'properties', 'presentation_style', 'size', 'settings'],
            'source',
            'fields_count',
        ])
        ->assertJsonPath('form.presentation_style', 'focused')
        ->assertJsonPath('form.size', 'lg')
        ->assertJsonPath('form.settings.navigation_arrows', true)
        ->assertJsonPath('source', 'typeform');
});

it('does not persist a form record on import', function () {
    Http::fake([
        '*.typeform.com/*' => Http::response(typeformHtmlFixture(), 200),
    ]);

    $countBefore = \App\Models\Forms\Form::count();

    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => ['url' => 'https://example.typeform.com/to/abc123'],
    ])->assertSuccessful();

    expect(\App\Models\Forms\Form::count())->toBe($countBefore);
});

it('rejects import with missing URL', function () {
    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => [],
    ])->assertStatus(422);
});

it('rejects import with unsupported source', function () {
    $this->postJson(route('open.forms.import'), [
        'source' => 'notion',
        'import_data' => ['url' => 'https://notion.so/form/abc'],
    ])->assertStatus(422);
});

it('allows unauthenticated URL-based import requests', function () {
    Http::fake([
        '*.typeform.com/*' => Http::response(typeformHtmlFixture(), 200),
    ]);

    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => ['url' => 'https://example.typeform.com/to/abc123'],
    ])->assertSuccessful();
});

it('returns the importer exception message with a 422', function () {
    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => ['url' => 'https://example.typeform.com/no-form-id-here'],
    ])
        ->assertStatus(422)
        ->assertJsonPath('type', 'error')
        ->assertJsonPath('message', 'Could not extract form ID from Typeform URL. Expected format: https://yourname.typeform.com/to/FORM_ID');
});

it('returns a generic 500 when the importer fails unexpectedly', function () {
    $this->mock(\App\Service\FormImport\FormImportService::class, function ($mock) {
        $mock->shouldReceive('import')
            ->once()
            ->andThrow(new \RuntimeException('exploded'));
    });

    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => ['url' => 'https://example.typeform.com/to/abc123'],
    ])
        ->assertStatus(500)
        ->assertJsonPath('type', 'error')
        ->assertJsonPath('message', 'An unexpected error occurred while importing the form. Please try again.');
});

it('rejects import with a malformed URL', function () {
    $this->postJson(route('open.forms.import'), [
        'source' => 'typeform',
        'import_data' => ['url' => 'not-a-url'],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('import_data.url');
});

// ---------------------------------------------------------------------------
// Typeform importer
// ---------------------------------------------------------------------------

describe('TypeformImporter', function () {
    it('maps basic Typeform fields correctly', function () {
        Http::fake([
            '*.typeform.com/*' => Http::response(typeformHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        expect($result['title'])->toBe('Test Contact Form');
        expect($result['properties'])->toHaveCount(4);
        expect($result['presentation_style'])->toBe('focused');
        expect($result['size'])->toBe('lg');
        expect($result['settings']['navigation_arrows'])->toBeTrue();

        $types = array_column($result['properties'], 'type');
        expect($types)->toBe(['text', 'email', 'text', 'select']);
    });

    it('flattens composite fields without adding page breaks', function () {
        $fixture = typeformFormData();
        $fixture['fields'][] = [
            'type' => 'contact_info',
            'title' => 'Contact',
            'properties' => [
                'fields' => [
                    ['type' => 'short_text', 'title' => 'First Name', 'validations' => ['required' => true]],
                    ['type' => 'email', 'title' => 'Email', 'validations' => ['required' => true]],
                ],
            ],
        ];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        $types = array_column($result['properties'], 'type');
        expect($types)->not->toContain('nf-page-break');
        expect($types)->toContain('email');
    });

    it('does not add page breaks for group composite fields', function () {
        $fixture = typeformFormData();
        $fixture['fields'][] = [
            'type' => 'group',
            'title' => 'Grouped Questions',
            'properties' => [
                'fields' => [
                    ['type' => 'short_text', 'title' => 'Company', 'validations' => ['required' => true]],
                    ['type' => 'number', 'title' => 'Team Size', 'validations' => ['required' => false]],
                ],
            ],
        ];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        $types = array_column($result['properties'], 'type');
        expect($types)->not->toContain('nf-page-break');
        expect($types)->toContain('number');
    });

    it('maps multiple choice with allow_multiple_selection', function () {
        $fixture = typeformFormData();
        $fixture['fields'][] = [
            'type' => 'multiple_choice',
            'title' => 'Colors',
            'validations' => ['required' => false],
            'properties' => [
                'allow_multiple_selection' => true,
                'choices' => [
                    ['label' => 'Red'],
                    ['label' => 'Blue'],
                ],
            ],
        ];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        $multi = collect($result['properties'])->firstWhere('name', 'Colors');
        expect($multi['type'])->toBe('multi_select');
        expect($multi['multi_select']['options'])->toHaveCount(2);
    });

    it('imports welcome screen as an nf-text block', function () {
        $fixture = typeformFormData();
        $fixture['welcome_screens'] = [[
            'id' => 'w1',
            'ref' => 'welcome-ref',
            'title' => "Welcome!\nThanks for stopping by.",
            'properties' => [
                'show_button' => true,
                'button_text' => 'Start',
                'description' => 'Please read this before starting.',
            ],
        ]];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        $welcome = $result['properties'][0];
        expect($welcome['type'])->toBe('nf-text');
        expect($welcome['content'])->toContain('Welcome!');
        expect($welcome['content'])->toContain('Please read this before starting.');
        // Newlines in the title should survive as <br>.
        expect($welcome['content'])->toContain('<br');
    });

    it('imports thank-you screens into submitted_text and skips the default one', function () {
        $fixture = typeformFormData();
        $fixture['thankyou_screens'] = [
            [
                'id' => 'ty1',
                'ref' => 'author-defined',
                'type' => 'thankyou_screen',
                'title' => 'Thanks — we will reach out soon!',
                'properties' => [
                    'description' => 'Expect a reply within 2 business days.',
                ],
            ],
            // Generic screen Typeform appends to every form; must be dropped.
            [
                'id' => 'DefaultTyScreen',
                'ref' => 'default_tys',
                'type' => 'thankyou_screen',
                'title' => "Thanks for completing this typeform\nNow *create your own*",
                'properties' => [],
            ],
        ];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        expect($result)->toHaveKey('submitted_text');
        expect($result['submitted_text'])->toContain('Thanks — we will reach out soon!');
        expect($result['submitted_text'])->toContain('Expect a reply within 2 business days.');
        expect($result['submitted_text'])->not->toContain('create your own');
    });

    it('keeps long Typeform dropdowns as a real dropdown (not a flat list)', function () {
        $choices = array_map(
            fn ($i) => ['label' => "Option {$i}"],
            range(1, 16)
        );

        $fixture = typeformFormData();
        $fixture['fields'] = [[
            'type' => 'dropdown',
            'title' => 'Country',
            'validations' => ['required' => true],
            'properties' => ['choices' => $choices],
        ]];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        $dropdown = collect($result['properties'])->firstWhere('name', 'Country');
        expect($dropdown['type'])->toBe('select');
        expect($dropdown['select']['options'])->toHaveCount(16);
        // Crucial: do NOT expand a 16-option list into radio buttons — that
        // would make the imported form unusable.
        expect($dropdown)->not->toHaveKey('without_dropdown');
        expect($dropdown['use_focused_selector'])->toBeFalse();
    });

    it('keeps short Typeform dropdowns as a real dropdown', function () {
        $fixture = typeformFormData();
        $fixture['fields'] = [[
            'type' => 'dropdown',
            'title' => 'Priority',
            'validations' => ['required' => false],
            'properties' => [
                'choices' => [
                    ['label' => 'Low'],
                    ['label' => 'Medium'],
                    ['label' => 'High'],
                ],
            ],
        ]];

        Http::fake([
            '*.typeform.com/*' => Http::response(wrapTypeformHtml($fixture), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://example.typeform.com/to/abc123']);

        $dropdown = collect($result['properties'])->firstWhere('name', 'Priority');
        expect($dropdown['type'])->toBe('select');
        expect($dropdown['select']['options'])->toHaveCount(3);
        expect($dropdown)->not->toHaveKey('without_dropdown');
        expect($dropdown['use_focused_selector'])->toBeFalse();
    });

    it('imports the real Typeform CSAT form cleanly', function () {
        Http::fake([
            '*.typeform.com/*' => Http::response(typeformCsatHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $result = $importer->import(['url' => 'https://3xxbbppenpx.typeform.com/to/DHTu6yrw']);

        expect($result['title'])->toBe('Customer Satisfaction Feedback Form');
        expect($result['presentation_style'])->toBe('focused');

        // Welcome screen is the first property.
        $first = $result['properties'][0];
        expect($first['type'])->toBe('nf-text');
        expect($first['content'])->toContain('Thank you for choosing our product/service');

        // Author's thank-you text survives; the default Typeform blurb does not.
        expect($result['submitted_text'])->toContain('Thank you for providing feedback');
        expect($result['submitted_text'])->not->toContain('create your own');

        // 16-option country dropdown stays as a proper dropdown.
        $country = collect($result['properties'])
            ->firstWhere(fn ($p) => str_contains($p['name'] ?? '', 'country'));
        expect($country['type'])->toBe('select');
        expect($country['select']['options'])->toHaveCount(16);
        expect($country)->not->toHaveKey('without_dropdown');
    });

    it('throws on 404 from Typeform page', function () {
        Http::fake([
            '*.typeform.com/*' => Http::response('Not found', 404),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $importer->import(['url' => 'https://example.typeform.com/to/bad']);
    })->throws(\App\Service\FormImport\FormImportException::class);

    it('throws on invalid Typeform URL format', function () {
        $importer = app(\App\Service\FormImport\Importers\TypeformImporter::class);
        $importer->import(['url' => 'https://example.typeform.com/signup']);
    })->throws(\App\Service\FormImport\FormImportException::class);
});

// ---------------------------------------------------------------------------
// Tally importer
// ---------------------------------------------------------------------------

describe('TallyImporter', function () {
    it('maps basic Tally fields from __NEXT_DATA__', function () {
        Http::fake([
            'tally.so/*' => Http::response(tallyHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/testform']);

        expect($result['title'])->toBe('Tally Test Form');
        expect($result['properties'])->not->toBeEmpty();

        $types = array_column($result['properties'], 'type');
        expect($types)->toContain('text');
        expect($types)->toContain('email');
    });

    it('synthesizes parent for child-only MULTI_SELECT blocks', function () {
        $blocks = tallyBlocks();
        $blocks[] = ['uuid' => 't1', 'type' => 'TITLE', 'groupUuid' => 'q1', 'groupType' => 'QUESTION', 'payload' => ['safeHTMLSchema' => [['Favorite']]]];
        $blocks[] = ['uuid' => 'o1', 'type' => 'MULTI_SELECT_OPTION', 'groupUuid' => 'ms1', 'groupType' => 'MULTI_SELECT', 'payload' => ['index' => 0, 'isRequired' => true, 'text' => 'Opt A']];
        $blocks[] = ['uuid' => 'o2', 'type' => 'MULTI_SELECT_OPTION', 'groupUuid' => 'ms1', 'groupType' => 'MULTI_SELECT', 'payload' => ['index' => 1, 'isRequired' => true, 'text' => 'Opt B']];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Multi Select Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/ms']);

        $multiSelect = collect($result['properties'])->firstWhere('type', 'multi_select');
        expect($multiSelect)->not->toBeNull();
        expect($multiSelect['multi_select']['options'])->toHaveCount(2);
    });

    it('does not duplicate matrix when parent block exists', function () {
        $blocks = tallyBlocks();
        $blocks[] = ['uuid' => 'm1', 'type' => 'MATRIX', 'groupUuid' => 'mg1', 'groupType' => 'MATRIX', 'payload' => ['isRequired' => true, 'name' => 'Grid']];
        $blocks[] = ['uuid' => 'mr1', 'type' => 'MATRIX_ROW', 'groupUuid' => 'mg1', 'groupType' => 'MATRIX', 'payload' => ['index' => 0, 'safeHTMLSchema' => [['R1']]]];
        $blocks[] = ['uuid' => 'mc1', 'type' => 'MATRIX_COLUMN', 'groupUuid' => 'mg1', 'groupType' => 'MATRIX', 'payload' => ['index' => 0, 'safeHTMLSchema' => [['C1']]]];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Matrix Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/mx']);

        $matrices = collect($result['properties'])->where('type', 'matrix')->values();
        expect($matrices)->toHaveCount(1);
    });

    it('imports the real Tally lead-generation form cleanly', function () {
        Http::fake([
            'tally.so/*' => Http::response(tallyLeadGenHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/wMGppm']);

        expect($result['title'])->toBe('Lead generation form');

        $names = array_column($result['properties'], 'name');
        $types = array_column($result['properties'], 'type');

        // Standalone LABEL widgets are consumed by the following input
        // instead of leaking through as nf-text / Untitled.
        expect($names)->not->toContain('Untitled');
        expect(array_count_values($types)['nf-text'] ?? 0)->toBe(0);

        $byName = collect($result['properties'])->keyBy('name');
        expect($byName->has('Company name'))->toBeTrue();
        expect($byName->get('Company name')['type'])->toBe('text');
        expect($byName->has('Company size'))->toBeTrue();
        expect($byName->get('Company size')['type'])->toBe('select');
        expect($byName->get('Company size')['select']['options'])->toHaveCount(4);

        // Standalone CHECKBOX block (newsletter opt-in) → real boolean checkbox
        $checkbox = collect($result['properties'])->firstWhere('type', 'checkbox');
        expect($checkbox)->not->toBeNull();
        expect($checkbox['name'])->toContain('newsletter');
        expect($checkbox)->not->toHaveKey('multi_select');

        // Thank-you page content ends up as submitted_text, not as properties
        expect($result)->toHaveKey('submitted_text');
        expect($result['submitted_text'])->toContain('Thanks for downloading');
        expect($result['submitted_text'])->toContain('<a href="https://tally.so/"');

        expect(
            collect($result['properties'])->contains(fn ($p) => str_contains($p['name'] ?? '', 'Thanks for downloading'))
        )->toBeFalse();
    });

    it('maps a standalone CHECKBOX block to a real checkbox field', function () {
        $blocks = tallyBlocks();
        $blocks[] = [
            'uuid' => 'c1',
            'type' => 'CHECKBOX',
            'groupUuid' => 'cg1',
            'groupType' => 'CHECKBOXES',
            'payload' => ['index' => 0, 'isFirst' => true, 'isLast' => true, 'text' => 'I agree to the terms'],
        ];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Consent Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/consent']);

        $checkbox = collect($result['properties'])->firstWhere('type', 'checkbox');
        expect($checkbox)->not->toBeNull();
        expect($checkbox['name'])->toBe('I agree to the terms');
    });

    it('uses standalone LABEL widgets as the next input name', function () {
        $blocks = tallyBlocks();
        $blocks[] = ['uuid' => 'l1', 'type' => 'LABEL', 'groupUuid' => 'lg1', 'groupType' => 'LABEL', 'payload' => ['safeHTMLSchema' => [['Company name']]]];
        $blocks[] = ['uuid' => 'i1', 'type' => 'INPUT_TEXT', 'groupUuid' => 'ig1', 'groupType' => 'INPUT_TEXT', 'payload' => ['placeholder' => '']];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Label Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/lbl']);

        $types = array_column($result['properties'], 'type');
        expect($types)->not->toContain('nf-text');

        $named = collect($result['properties'])->firstWhere('name', 'Company name');
        expect($named)->not->toBeNull();
        expect($named['type'])->toBe('text');
    });

    it('keeps a Tally dropdown as a dropdown even with few options', function () {
        $blocks = tallyBlocks();
        // A 3-option DROPDOWN — pre-fix this would have been flattened into a
        // radio list because count <= 5, discarding the author's intent.
        $blocks[] = ['uuid' => 'd1', 'type' => 'DROPDOWN', 'groupUuid' => 'dg1', 'groupType' => 'DROPDOWN', 'payload' => ['name' => 'Priority', 'isRequired' => false]];
        $blocks[] = ['uuid' => 'do1', 'type' => 'DROPDOWN_OPTION', 'groupUuid' => 'dg1', 'groupType' => 'DROPDOWN', 'payload' => ['index' => 0, 'text' => 'Low']];
        $blocks[] = ['uuid' => 'do2', 'type' => 'DROPDOWN_OPTION', 'groupUuid' => 'dg1', 'groupType' => 'DROPDOWN', 'payload' => ['index' => 1, 'text' => 'Medium']];
        $blocks[] = ['uuid' => 'do3', 'type' => 'DROPDOWN_OPTION', 'groupUuid' => 'dg1', 'groupType' => 'DROPDOWN', 'payload' => ['index' => 2, 'text' => 'High']];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Dropdown Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/dd']);

        $priority = collect($result['properties'])->firstWhere('name', 'Priority');
        expect($priority['type'])->toBe('select');
        expect($priority['select']['options'])->toHaveCount(3);
        expect($priority)->not->toHaveKey('without_dropdown');
    });

    it('keeps long Tally dropdowns as a real dropdown', function () {
        $blocks = tallyBlocks();
        $blocks[] = ['uuid' => 'd1', 'type' => 'DROPDOWN', 'groupUuid' => 'dg1', 'groupType' => 'DROPDOWN', 'payload' => ['name' => 'Country', 'isRequired' => true]];
        foreach (range(1, 16) as $i) {
            $blocks[] = ['uuid' => "do{$i}", 'type' => 'DROPDOWN_OPTION', 'groupUuid' => 'dg1', 'groupType' => 'DROPDOWN', 'payload' => ['index' => $i - 1, 'text' => "Country {$i}"]];
        }

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Long Dropdown Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/long']);

        $country = collect($result['properties'])->firstWhere('name', 'Country');
        expect($country['type'])->toBe('select');
        expect($country['select']['options'])->toHaveCount(16);
        expect($country)->not->toHaveKey('without_dropdown');
    });

    it('imports a leading Tally TEXT block as nf-text intro content', function () {
        // A TEXT block that is NOT a LABEL/QUESTION heading is the form's
        // intro copy — it must survive as editable nf-text, not be consumed
        // into the next input's name.
        $blocks = [
            ['uuid' => 'intro', 'type' => 'TEXT', 'groupUuid' => 'intro', 'groupType' => 'TEXT', 'payload' => ['safeHTMLSchema' => [['Welcome to our beta program — please introduce yourself.']]]],
            ['uuid' => 'f1', 'type' => 'INPUT_TEXT', 'groupUuid' => 'f1', 'groupType' => 'INPUT_TEXT', 'payload' => ['placeholder' => 'Name']],
        ];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Intro Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/intro']);

        $first = $result['properties'][0];
        expect($first['type'])->toBe('nf-text');
        expect($first['content'])->toContain('Welcome to our beta program');

        // The INPUT_TEXT still comes through with its own name (not consumed).
        $name = collect($result['properties'])->firstWhere('type', 'text');
        expect($name)->not->toBeNull();
        expect($name['name'])->toBe('Name');
    });

    it('stops at thank-you page break', function () {
        $blocks = tallyBlocks();
        $blocks[] = ['uuid' => 'pb1', 'type' => 'PAGE_BREAK', 'groupUuid' => 'pg1', 'groupType' => 'PAGE_BREAK', 'payload' => ['isThankYouPage' => true]];
        $blocks[] = ['uuid' => 'after', 'type' => 'INPUT_TEXT', 'groupUuid' => 'after', 'groupType' => 'INPUT_TEXT', 'payload' => ['placeholder' => 'Hidden']];

        Http::fake([
            'tally.so/*' => Http::response(wrapTallyBlocks('Thank You Form', $blocks), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\TallyImporter::class);
        $result = $importer->import(['url' => 'https://tally.so/r/ty']);

        $names = array_column($result['properties'], 'name');
        expect($names)->not->toContain('Hidden');
    });
});

// ---------------------------------------------------------------------------
// Fillout importer
// ---------------------------------------------------------------------------

describe('FilloutImporter', function () {
    it('maps basic Fillout fields', function () {
        Http::fake([
            'fillout.com/*' => Http::response(filloutHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\FilloutImporter::class);
        $result = $importer->import(['url' => 'https://example.fillout.com/t/abc123']);

        expect($result['title'])->toBe('Fillout Test Form');
        expect($result['properties'])->not->toBeEmpty();

        $types = array_column($result['properties'], 'type');
        expect($types)->toContain('text');
    });

    it('keeps long Fillout dropdowns as a real dropdown', function () {
        $options = array_map(
            fn ($i) => ['label' => "Country {$i}"],
            range(1, 16)
        );
        $pageProps = [
            'flow' => ['name' => 'Country Form'],
            'flowSnapshot' => [
                'template' => [
                    'firstStep' => 's1',
                    'steps' => [
                        's1' => [
                            'id' => 's1', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => ''],
                            'template' => ['widgets' => [
                                [
                                    'type' => 'Dropdown',
                                    'position' => ['row' => 0],
                                    'template' => [
                                        'label' => 'Country',
                                        'options' => ['staticOptions' => $options],
                                    ],
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            'fillout.com/*' => Http::response(wrapFilloutPageProps($pageProps), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\FilloutImporter::class);
        $result = $importer->import(['url' => 'https://example.fillout.com/t/dd']);

        $country = collect($result['properties'])->firstWhere('name', 'Country');
        expect($country['type'])->toBe('select');
        expect($country['select']['options'])->toHaveCount(16);
        expect($country)->not->toHaveKey('without_dropdown');
    });

    it('imports the real Fillout registration form cleanly', function () {
        Http::fake([
            'fillout.com/*' => Http::response(filloutRegistrationHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\FilloutImporter::class);
        $result = $importer->import(['url' => 'https://example.fillout.com/t/88efTSDCqTus']);

        expect($result['title'])->toBe('Online Registration Form Template 510');

        // Step order follows firstStep → nextStep (button or step-level),
        // not the insertion order of the steps dictionary.
        $names = array_column($result['properties'], 'name');
        $orderedFieldNames = array_values(array_filter($names, fn ($n) => $n !== 'Page Break' && $n !== 'Register with us online!'));
        expect($orderedFieldNames[0])->toBe("What's your full name?");
        expect($orderedFieldNames[1])->toBe("What's your primary email address?");
        expect($orderedFieldNames[2])->toBe("Anything else you'd like to share?");

        // Focused presentation paginates one field at a time, so we drop the
        // explicit nf-page-break blocks that would otherwise produce empty
        // intermediate pages.
        expect(array_count_values(array_column($result['properties'], 'type'))['nf-page-break'] ?? 0)->toBe(0);

        // Cover welcome screen survives as an nf-text block.
        $cover = collect($result['properties'])->firstWhere('type', 'nf-text');
        expect($cover)->not->toBeNull();
        expect($cover['content'])->toContain('Register with us online!');

        // Thank-you screen is mapped to submitted_text with placeholder
        // reference tokens stripped out so the copy reads cleanly.
        expect($result)->toHaveKey('submitted_text');
        expect($result['submitted_text'])->toContain("You're officially registered");
        expect($result['submitted_text'])->not->toMatch('/\{\{[^}]+\}\}/');

        // Nice-to-have: page-per-question flow → focused layout hint.
        expect($result['presentation_style'] ?? null)->toBe('focused');
        expect($result['size'] ?? null)->toBe('lg');

        // No leftover Button widgets or blank nf-text banners.
        expect($names)->not->toContain('Untitled button field');
        foreach ($result['properties'] as $p) {
            if (($p['type'] ?? '') === 'nf-text') {
                expect(trim(strip_tags($p['content'] ?? '')))->not->toBe('');
            }
        }
    });

    it('keeps page breaks when the layout stays classic (non-focused)', function () {
        $pageProps = [
            'flow' => ['name' => 'Classic Form'],
            'flowSnapshot' => [
                'template' => [
                    'firstStep' => 's1',
                    'steps' => [
                        's1' => [
                            'id' => 's1', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => 's2'],
                            'template' => ['widgets' => [
                                ['type' => 'ShortAnswer', 'position' => ['row' => 0], 'template' => ['label' => 'A']],
                                ['type' => 'ShortAnswer', 'position' => ['row' => 1], 'template' => ['label' => 'B']],
                                ['type' => 'ShortAnswer', 'position' => ['row' => 2], 'template' => ['label' => 'C']],
                            ]],
                        ],
                        's2' => [
                            'id' => 's2', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => ''],
                            'template' => ['widgets' => [
                                ['type' => 'EmailInput', 'position' => ['row' => 0], 'template' => ['label' => 'Email']],
                                ['type' => 'ShortAnswer', 'position' => ['row' => 1], 'template' => ['label' => 'Phone']],
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            'fillout.com/*' => Http::response(wrapFilloutPageProps($pageProps), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\FilloutImporter::class);
        $result = $importer->import(['url' => 'https://example.fillout.com/t/classic']);

        $types = array_column($result['properties'], 'type');
        expect(array_count_values($types)['nf-page-break'] ?? 0)->toBe(1);
    });

    it('drops page breaks when focused layout is inferred', function () {
        $pageProps = [
            'flow' => ['name' => 'Focused Form'],
            'flowSnapshot' => [
                'template' => [
                    'firstStep' => 's1',
                    'steps' => [
                        's1' => [
                            'id' => 's1', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => 's2'],
                            'template' => ['widgets' => [
                                ['type' => 'ShortAnswer', 'position' => ['row' => 0], 'template' => ['label' => 'Name']],
                            ]],
                        ],
                        's2' => [
                            'id' => 's2', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => ''],
                            'template' => ['widgets' => [
                                ['type' => 'EmailInput', 'position' => ['row' => 0], 'template' => ['label' => 'Email']],
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            'fillout.com/*' => Http::response(wrapFilloutPageProps($pageProps), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\FilloutImporter::class);
        $result = $importer->import(['url' => 'https://example.fillout.com/t/focused']);

        expect($result['presentation_style'] ?? null)->toBe('focused');
        $types = array_column($result['properties'], 'type');
        expect(array_count_values($types)['nf-page-break'] ?? 0)->toBe(0);
    });

    it('does not infer focused layout when steps have multiple fields', function () {
        $pageProps = [
            'flow' => ['name' => 'Wide Form'],
            'flowSnapshot' => [
                'template' => [
                    'firstStep' => 's1',
                    'steps' => [
                        's1' => [
                            'id' => 's1', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => 's2'],
                            'template' => ['widgets' => [
                                ['type' => 'ShortAnswer', 'position' => ['row' => 0], 'template' => ['label' => 'A']],
                                ['type' => 'ShortAnswer', 'position' => ['row' => 1], 'template' => ['label' => 'B']],
                                ['type' => 'ShortAnswer', 'position' => ['row' => 2], 'template' => ['label' => 'C']],
                            ]],
                        ],
                        's2' => [
                            'id' => 's2', 'type' => 'form',
                            'nextStep' => ['defaultNextStep' => ''],
                            'template' => ['widgets' => [
                                ['type' => 'EmailInput', 'position' => ['row' => 0], 'template' => ['label' => 'Email']],
                                ['type' => 'ShortAnswer', 'position' => ['row' => 1], 'template' => ['label' => 'Phone']],
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            'fillout.com/*' => Http::response(wrapFilloutPageProps($pageProps), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\FilloutImporter::class);
        $result = $importer->import(['url' => 'https://example.fillout.com/t/wide']);

        expect($result)->not->toHaveKey('presentation_style');
        expect($result)->not->toHaveKey('size');
    });
});

// ---------------------------------------------------------------------------
// Google Forms importer
// ---------------------------------------------------------------------------

describe('GoogleFormsImporter', function () {
    it('maps Google Forms public page fields', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(googleFormsPublicHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        expect($result['title'])->toBe('Google Test Form');
        expect($result['properties'])->toHaveCount(4);

        $types = array_column($result['properties'], 'type');
        expect($types)->toBe(['text', 'select', 'date', 'text']);

        $name = $result['properties'][0];
        expect($name['required'])->toBeTrue();
        expect($name['help'])->toBe('Enter your full name');

        $department = collect($result['properties'])->firstWhere('name', 'Department');
        expect($department['type'])->toBe('select');
        expect($department['select']['options'])->toHaveCount(3);
        expect($department)->not->toHaveKey('without_dropdown');
    });

    it('prefers form_id over a URL when both are given', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(googleFormsPublicHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'form_id' => '1abc123',
            'url' => 'https://docs.google.com/forms/d/2xyz999/edit',
        ]);

        expect($result['title'])->toBe('Google Test Form');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://docs.google.com/forms/d/e/1abc123/viewform';
        });
    });

    it('renders Google Forms description as an intro nf-text block', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture(['description' => 'Please answer honestly — your feedback improves the product.']),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        // Description shows up first, as an editable nf-text block.
        $first = $result['properties'][0];
        expect($first['type'])->toBe('nf-text');
        expect($first['content'])->toContain('Please answer honestly');
        expect($result['properties'])->toHaveCount(5);
    });

    it('keeps long DROP_DOWN questions as a real dropdown', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[9, 'Country', '', 3, [[10, array_map(fn ($i) => ["Country {$i}", 0], range(1, 16)), 1]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $country = collect($result['properties'])->firstWhere('name', 'Country');
        expect($country['type'])->toBe('select');
        expect($country['select']['options'])->toHaveCount(16);
        expect($country)->not->toHaveKey('without_dropdown');
    });

    it('keeps short DROP_DOWN questions as a real dropdown', function () {
        // Author explicitly picked a dropdown — do not flatten into radio.
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[11, 'Priority', '', 3, [[12, [['Low', 0], ['Medium', 0], ['High', 0]], 0]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $priority = collect($result['properties'])->firstWhere('name', 'Priority');
        expect($priority['type'])->toBe('select');
        expect($priority)->not->toHaveKey('without_dropdown');
    });

    it('maps radio questions to a radio-style select', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[13, 'Satisfaction', '', 2, [[14, [['Good', 0], ['Ok', 0], ['Bad', 0]], 1]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $satisfaction = collect($result['properties'])->firstWhere('name', 'Satisfaction');
        expect($satisfaction['type'])->toBe('select');
        expect($satisfaction['without_dropdown'])->toBeTrue();
        expect($satisfaction['select']['options'])->toHaveCount(3);
        expect($satisfaction['required'])->toBeTrue();
    });

    it('maps a radio "Other" option to a regular option', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[14, 'Satisfaction', '', 2, [[15, [['Good', 0], ['', 0, null, null, 1], ['Bad', 0]], 0]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $satisfaction = collect($result['properties'])->firstWhere('name', 'Satisfaction');
        $options = array_column($satisfaction['select']['options'], 'name');
        expect($options)->toBe(['Good', 'Other', 'Bad']);
    });

    it('maps grid questions to matrix', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[
                        15, 'Satisfaction', '', 7,
                        [
                            [101, [['Good'], ['Bad']], 1, ['Service']],
                            [102, [['Good'], ['Bad']], 1, ['Price']],
                        ],
                    ]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $matrix = collect($result['properties'])->firstWhere('type', 'matrix');
        expect($matrix)->not->toBeNull();
        expect($matrix['rows'])->toBe(['Service', 'Price']);
        expect($matrix['columns'])->toBe(['Good', 'Bad']);
    });

    it('maps checkbox grid questions to matrix', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[
                        16, 'Features', '', 10,
                        [
                            [201, [['Yes'], ['No']], 0, ['Email reports']],
                            [202, [['Yes'], ['No']], 0, ['SMS alerts']],
                        ],
                    ]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $matrix = collect($result['properties'])->firstWhere('type', 'matrix');
        expect($matrix)->not->toBeNull();
        expect($matrix['rows'])->toBe(['Email reports', 'SMS alerts']);
        expect($matrix['columns'])->toBe(['Yes', 'No']);
    });

    it('maps scale questions with labels', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[16, 'Rating', '', 5, [[17, [['1'], ['2'], ['3'], ['4'], ['5']], 0, ['Low', 'High']]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $rating = collect($result['properties'])->firstWhere('name', 'Rating');
        expect($rating['type'])->toBe('scale');
        expect($rating['scale_min_value'])->toBe(1);
        expect($rating['scale_max_value'])->toBe(5);
        expect($rating['help'])->toBe('Low → High');
    });

    it('derives scale range from the point labels', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[17, 'Effort', '', 5, [[18, [['0'], ['1'], ['2'], ['3'], ['4'], ['5'], ['6'], ['7'], ['8'], ['9'], ['10']], 1, []]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $effort = collect($result['properties'])->firstWhere('name', 'Effort');
        expect($effort['type'])->toBe('scale');
        expect($effort['scale_min_value'])->toBe(0);
        expect($effort['scale_max_value'])->toBe(10);
        expect($effort['required'])->toBeTrue();
        expect($effort)->not->toHaveKey('help');
    });

    it('maps date questions (type 9) to a date field', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[18, 'Delivery date', '', 9, [[19, null, 0]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $date = collect($result['properties'])->firstWhere('name', 'Delivery date');
        expect($date)->not->toBeNull();
        expect($date['type'])->toBe('date');
    });

    it('maps section header description into the nf-text block', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[19, 'About you', 'Please tell us a little about yourself.', 6, null]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $header = collect($result['properties'])->firstWhere('type', 'nf-text');
        expect($header)->not->toBeNull();
        expect($header['content'])->toContain('About you');
        expect($header['content'])->toContain('Please tell us a little about yourself.');
    });

    it('throws when the page has no form data', function () {
        Http::fake([
            'docs.google.com/*' => Http::response('<html><body><h1>Not a form</h1></body></html>', 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);
    })->throws(\App\Service\FormImport\FormImportException::class, 'Could not find form data');

    it('throws when neither form_id nor url is provided', function () {
        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $importer->import([]);
    })->throws(\App\Service\FormImport\FormImportException::class, 'Could not extract a form ID');

    it('imports published viewform URLs during import', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(googleFormsPublicHtmlFixture(), 200),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/e/published123/viewform',
        ]);

        expect($result['title'])->toBe('Google Test Form');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://docs.google.com/forms/d/e/published123/viewform';
        });
    });

    it('parses option labels that contain a literal ]; sequence', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(
                googleFormsPublicHtmlFixture([
                    'items' => [[3, 'Department', '', 3, [[4, [['Sales [Q1]; [Q2]', 0], ['Support', 0]], 1]]]],
                ]),
                200
            ),
        ]);

        $importer = app(\App\Service\FormImport\Importers\GoogleFormsImporter::class);
        $result = $importer->import([
            'url' => 'https://docs.google.com/forms/d/1abc123/edit',
        ]);

        $department = collect($result['properties'])->firstWhere('name', 'Department');
        expect($department['select']['options'][0]['name'])->toBe('Sales [Q1]; [Q2]');
        expect($department['select']['options'][1]['name'])->toBe('Support');
    });
});

// ---------------------------------------------------------------------------
// Google Forms controller flow
// ---------------------------------------------------------------------------

describe('Google Forms controller flow', function () {
    it('imports a Google Form via public URL', function () {
        Http::fake([
            'docs.google.com/*' => Http::response(googleFormsPublicHtmlFixture(), 200),
        ]);

        $this->postJson(route('open.forms.import'), [
            'source' => 'google_forms',
            'import_data' => ['url' => 'https://docs.google.com/forms/d/1abc123/edit'],
        ])
            ->assertSuccessful()
            ->assertJsonPath('form.title', 'Google Test Form');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://docs.google.com/forms/d/e/1abc123/viewform';
        });
    });

    it('returns an error when neither form_id nor url is given', function () {
        $this->postJson(route('open.forms.import'), [
            'source' => 'google_forms',
            'import_data' => [],
        ])->assertStatus(422);
    });

    it('returns 422 when form_id is empty and no url is given', function () {
        $this->postJson(route('open.forms.import'), [
            'source' => 'google_forms',
            'import_data' => ['form_id' => ''],
        ])->assertStatus(422);
    });
});

// ---------------------------------------------------------------------------
// Test fixtures
// ---------------------------------------------------------------------------

function typeformFormData(): array
{
    return [
        'title' => 'Test Contact Form',
        'fields' => [
            ['type' => 'short_text', 'title' => 'Name', 'validations' => ['required' => true]],
            ['type' => 'email', 'title' => 'Email', 'validations' => ['required' => true]],
            ['type' => 'long_text', 'title' => 'Message', 'validations' => ['required' => false]],
            [
                'type' => 'dropdown',
                'title' => 'Category',
                'validations' => ['required' => false],
                'properties' => [
                    'choices' => [
                        ['label' => 'Support'],
                        ['label' => 'Sales'],
                        ['label' => 'Other'],
                    ],
                ],
            ],
        ],
    ];
}

function wrapTypeformHtml(array $formData): string
{
    $formJson = json_encode($formData, JSON_UNESCAPED_UNICODE);

    return '<html><head></head><body><div id="root"></div>'
        . '<script data-csp-hash="">'
        . "window.rendererData={rootDomNode:'root',form:" . $formJson
        . ",messages:{},trackingInfo:{}};"
        . '</script></body></html>';
}

function typeformHtmlFixture(): string
{
    return wrapTypeformHtml(typeformFormData());
}

function typeformCsatHtmlFixture(): string
{
    $form = json_decode(
        file_get_contents(__DIR__ . '/../../fixtures/typeform-csat.json'),
        true
    );

    return wrapTypeformHtml($form);
}

function tallyBlocks(): array
{
    return [
        ['uuid' => 'ft1', 'type' => 'FORM_TITLE', 'groupUuid' => 'ft1', 'groupType' => 'FORM_TITLE', 'payload' => ['title' => 'Title']],
        ['uuid' => 'f1', 'type' => 'INPUT_TEXT', 'groupUuid' => 'f1', 'groupType' => 'INPUT_TEXT', 'payload' => ['placeholder' => 'Name']],
        ['uuid' => 'f2', 'type' => 'INPUT_EMAIL', 'groupUuid' => 'f2', 'groupType' => 'INPUT_EMAIL', 'payload' => ['placeholder' => 'Email']],
    ];
}

function wrapTallyBlocks(string $title, array $blocks): string
{
    $nextData = json_encode([
        'props' => [
            'pageProps' => [
                'name' => $title,
                'blocks' => $blocks,
            ],
        ],
    ]);

    return '<html><body><script id="__NEXT_DATA__" type="application/json">' . $nextData . '</script></body></html>';
}

function tallyHtmlFixture(): string
{
    return wrapTallyBlocks('Tally Test Form', tallyBlocks());
}

function tallyLeadGenHtmlFixture(): string
{
    $json = file_get_contents(__DIR__ . '/../../fixtures/tally-lead-generation.json');

    return '<html><body><script id="__NEXT_DATA__" type="application/json">' . $json . '</script></body></html>';
}

function filloutHtmlFixture(): string
{
    $nextData = json_encode([
        'props' => [
            'pageProps' => [
                'flow' => ['name' => 'Fillout Test Form'],
                'flowSnapshot' => [
                    'template' => [
                        'steps' => [
                            [
                                'template' => [
                                    'widgets' => [
                                        ['type' => 'ShortAnswer', 'name' => 'Your Name', 'required' => true],
                                        ['type' => 'EmailInput', 'name' => 'Your Email', 'required' => true],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    return '<html><body><script id="__NEXT_DATA__" type="application/json">' . $nextData . '</script></body></html>';
}

function wrapFilloutPageProps(array $pageProps): string
{
    $nextData = json_encode(['props' => ['pageProps' => $pageProps]], JSON_UNESCAPED_UNICODE);

    return '<html><body><script id="__NEXT_DATA__" type="application/json">' . $nextData . '</script></body></html>';
}

function filloutRegistrationHtmlFixture(): string
{
    $json = file_get_contents(__DIR__ . '/../../fixtures/fillout-registration.json');

    return '<html><body><script id="__NEXT_DATA__" type="application/json">' . $json . '</script></body></html>';
}

function googleFormsPublicHtmlFixture(array $overrides = []): string
{
    $defaults = [
        'title' => 'Google Test Form',
        'description' => '',
        'items' => [
            [1, 'Your Name', 'Enter your full name', 0, [[2, null, 1]]],
            [3, 'Department', '', 3, [[4, [['Sales', 0], ['Support', 0], ['Engineering', 0]], 1]]],
            [5, 'Start date', '', 9, [[6, null, 0]]],
            [7, 'Comments', '', 1, [[8, null, 0]]],
        ],
    ];

    $data = array_replace($defaults, $overrides);

    // Matches what Google emits on public pages: data[0] is null, data[1]
    // is the form array where index 0 is the description, index 1 the items
    // and index 8 the title. Sequential keys keep json_encode from turning
    // data[1] into an object.
    $form = [
        null,
        [$data['description'], $data['items'], 'Thank you for your response.', null, null, null, null, null, $data['title']],
    ];

    $formJson = json_encode($form, JSON_UNESCAPED_UNICODE);

    return '<html><head></head><body>'
        . '<form action="https://docs.google.com/forms/d/e/1abc123/formResponse">'
        . '<input name="entry.1">'
        . '</form>'
        . '<script>var FB_PUBLIC_LOAD_DATA_ = ' . $formJson . ';</script>'
        . '</body></html>';
}
