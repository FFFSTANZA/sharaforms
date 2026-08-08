<?php

use App\Service\AI\Prompts\Form\FormSchemaNormalizer;

uses(\Tests\TestCase::class);

it('strips scripts, event handlers and unsafe tags from nf-text content', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        [
            'type' => 'nf-text',
            'name' => 'Intro',
            'content' => '<script>alert(1)</script><p>Hello <b>there</b></p><img src="x" onerror="alert(2)"><a href="javascript:evil()">click</a><iframe src="https://evil.example"></iframe>',
        ],
    ]);

    $content = $properties[0]['content'] ?? '';
    expect($content)
        ->not->toContain('<script')
        ->not->toContain('onerror')
        ->not->toContain('javascript:')
        ->not->toContain('<iframe')
        ->toContain('<p>Hello <b>there</b></p>')
        ->toContain('<a href="#">click</a>');
});

it('drops content from nf-code blocks so the AI can not smuggle markup', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        [
            'type' => 'nf-code',
            'name' => 'Embed',
            'content' => '<script src="https://evil.example/x.js"></script>',
            'code' => 'evil',
        ],
    ]);

    expect($properties[0]['type'])->toBe('nf-code')
        ->and($properties[0])->not->toHaveKeys(['content', 'code']);
});

it('sanitizes submitted_text the same way as nf-text content', function () {
    $form = FormSchemaNormalizer::normalizeFormData([
        'submitted_text' => '<script>alert(1)</script><p>Thanks for <a href="https://ok.example">feedback</a></p>',
        'properties' => [],
    ]);

    expect($form['submitted_text'])
        ->not->toContain('<script')
        ->toContain('<p>')
        ->toContain('href="https://ok.example"');
});

it('keeps only safe color styles in allowed span tags', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        [
            'type' => 'nf-text',
            'name' => 'Intro',
            'content' => '<span style="color: #FF0000">red</span><span style="position: fixed; top: 0">bad</span>',
        ],
    ]);

    $content = $properties[0]['content'] ?? '';
    expect($content)
        ->toContain('style="color: #FF0000"')
        ->not->toContain('position: fixed');
});

it('leaves plain help text untouched', function () {
    $properties = FormSchemaNormalizer::normalizeProperties([
        [
            'type' => 'text',
            'name' => 'Bio',
            'help' => 'Hi there <3',
        ],
    ]);

    expect($properties[0]['help'])->toBe('Hi there <3');
});
