<?php

use App\Http\Requests\Integration\FormIntegrationsRequest;
use Illuminate\Validation\ValidationException;

uses(\Tests\TestCase::class);

it('rejects unknown integration ids with a 422 instead of a server error', function () {
    config()->set('database.default', 'sqlite');

    $httpRequest = Illuminate\Http\Request::create('/', 'POST', [
        'integration_id' => 'not_a_real_integration',
        'status' => 'active',
        'data' => [],
    ]);

    // FormIntegrationsRequest resolves the handler class in its constructor;
    // unknown ids must surface as a validation error, not an unhandled exception.
    expect(fn () => new FormIntegrationsRequest($httpRequest))
        ->toThrow(ValidationException::class);
});

it('resolves known integrations without error', function () {
    $httpRequest = Illuminate\Http\Request::create('/', 'POST', [
        'integration_id' => 'baserow',
        'status' => 'active',
        'data' => [],
    ]);

    $formRequest = new FormIntegrationsRequest($httpRequest);

    expect($formRequest->rules())->toHaveKey('data.api_key');
});
