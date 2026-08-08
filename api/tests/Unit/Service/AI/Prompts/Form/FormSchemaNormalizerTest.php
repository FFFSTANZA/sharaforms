<?php

use App\Service\AI\Prompts\Form\FormSchemaNormalizer;

uses(\Tests\TestCase::class);

it('maps form-level model aliases onto the app contract', function () {
    $form = FormSchemaNormalizer::normalizeFormData([
        'submit_text' => 'Send',
        'title_text' => 'Thanks for participating',
        'refill_button_text' => 'Again',
        'properties' => [],
    ]);

    expect($form['submit_button_text'])->toBe('Send')
        ->and($form['submitted_text'])->toBe('Thanks for participating')
        ->and($form['re_fill_button_text'])->toBe('Again')
        ->and($form)->not->toHaveKeys(['submit_text', 'title_text', 'refill_button_text']);
});

it('coerces boolean form keys from strings', function () {
    $form = FormSchemaNormalizer::normalizeFormData([
        're_fillable' => 'true',
        'use_captcha' => 'yes',
        'uppercase_labels' => 'on',
        'properties' => [],
    ]);

    expect($form['re_fillable'])->toBeTrue()
        ->and($form['use_captcha'])->toBeTrue()
        ->and($form['uppercase_labels'])->toBeTrue();
});

it('maps field-level aliases onto the app contract', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['label' => 'Your email', 'help_text' => 'We never share it.', 'type' => 'email', 'required' => 'true'],
    ]);

    $field = $properties[0];
    expect($field['name'])->toBe('Your email')
        ->and($field['help'])->toBe('We never share it.')
        ->and($field['required'])->toBeTrue();
});

it('defaults missing string and boolean keys', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['type' => 'text'],
    ]);

    $field = $properties[0];
    expect($field['name'])->toBe('')
        ->and($field['help'])->toBe('')
        ->and($field['placeholder'])->toBe('')
        ->and(array_key_exists('hidden', $field))->toBeFalse()
        ->and($field['width'])->toBe('full')
        ->and($field['multi_lines'])->toBeFalse()
        ->and($field['max_char_limit'])->toBe(500);
});

it('falls back to full width for invented widths', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['name' => 'A', 'type' => 'text', 'width' => 'half'],
    ]);

    expect($properties[0]['width'])->toBe('full');
});

it('accepts bare string options and option label shapes', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['name' => 'Pick', 'type' => 'select', 'options' => ['One', 'Two']],
        ['name' => 'Pick multi', 'type' => 'multi_select', 'multi_select' => ['options' => [['label' => 'Alpha'], ['name' => 'Beta', 'id' => 'b']]]],
    ]);

    $select = $properties[0];
    expect($select['select']['options'])->toBe([
        ['name' => 'One', 'id' => 'One'],
        ['name' => 'Two', 'id' => 'Two'],
    ])->and($select)->not->toHaveKey('options');

    $multi = $properties[1];
    expect($multi['multi_select']['options'])->toBe([
        ['name' => 'Alpha', 'id' => 'Alpha'],
        ['name' => 'Beta', 'id' => 'b'],
    ]);
});

it('provides a default option when the model sends an empty option list', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['name' => 'Pick', 'type' => 'select', 'select' => ['options' => []]],
    ]);

    expect($properties[0]['select']['options'])->toBe([['name' => 'Option 1', 'id' => 'option-1']]);
});

it('drops keys the models invent that are not part of the contract', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['name' => 'A', 'type' => 'number', 'min' => 1, 'max' => 10, 'accept' => 'image/*', 'format' => 'x'],
    ]);

    $field = $properties[0];
    expect($field)->not->toHaveKeys(['min', 'max', 'accept', 'format']);
});

it('applies type-specific defaults', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['name' => 'Rate', 'type' => 'rating'],
        ['name' => 'Scale', 'type' => 'scale', 'scale_min_value' => '1', 'scale_max_value' => '7'],
        ['name' => 'Bar', 'type' => 'barcode'],
        ['name' => 'Grid', 'type' => 'matrix', 'rows' => ['R1', 'R2'], 'columns' => ['Yes', 'No']],
        ['name' => 'Intro', 'type' => 'nf-text'],
    ]);

    expect($properties[0]['rating_max_value'])->toBe(5)
        ->and($properties[1]['scale_min_value'])->toBe(1)
        ->and($properties[1]['scale_max_value'])->toBe(7)
        ->and($properties[2]['decoders'])->toBe(['ean_reader', 'ean_8_reader'])
        ->and($properties[3]['rows'])->toBe(['R1', 'R2'])
        ->and($properties[3]['columns'])->toBe(['Yes', 'No'])
        ->and($properties[4]['content'])->toContain('Intro');
});

it('flattens a nested core wrapper used by some providers', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        ['core' => ['name' => 'Nested'], 'type' => 'text'],
    ]);

    expect($properties[0]['name'])->toBe('Nested');
});

it('keeps non-array property entries untouched', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([['type' => 'text'], 'bogus']);

    expect($properties[0])->toHaveKey('name')
        ->and($properties[1])->toBe('bogus');
});

it('normalizes a whole form including its properties in one call', function () {
    $form = FormSchemaNormalizer::normalizeFormData([
        'submit_text' => 'Go',
        'properties' => [
            ['label' => 'Name', 'type' => 'text', 'width' => '1/2'],
            ['name' => 'Note', 'type' => 'nf-text', 'content' => '<p>Hi</p>'],
        ],
    ]);

    expect($form['submit_button_text'])->toBe('Go')
        ->and($form['properties'][0]['name'])->toBe('Name')
        ->and($form['properties'][0]['width'])->toBe('1/2')
        ->and($form['properties'][1]['content'])->toBe('<p>Hi</p>');
});
