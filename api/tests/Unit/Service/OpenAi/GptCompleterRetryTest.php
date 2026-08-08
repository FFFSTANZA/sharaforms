<?php

use App\Service\OpenAi\GptCompleter;
use OpenAI\Exceptions\TransporterException;

uses(\Tests\TestCase::class);

it('retries when the provider transport throws a throwable error', function () {
    $completer = new class('gpt-5.4-mini', 'test-key', 2) extends GptCompleter {
        public int $calls = 0;

        protected function createCompletion(array $payload): mixed
        {
            $this->calls++;

            if ($this->calls === 1) {
                throw new TransporterException(
                    new \GuzzleHttp\Exception\ConnectException('Connection refused', new \GuzzleHttp\Psr7\Request('POST', 'test'))
                );
            }

            return (object) [
                'choices' => [
                    (object) [
                        'message' => (object) ['content' => '{"ok": true}'],
                    ],
                ],
                'usage' => null,
            ];
        }
    };

    $completer->completeChat([['role' => 'user', 'content' => 'Say ok']]);

    expect($completer->getArray())->toBe(['ok' => true])
        ->and($completer->calls)->toBe(2);
});

it('throws when the provider transport keeps failing', function () {
    $completer = new class('gpt-5.4-mini', 'test-key', 2) extends GptCompleter {
        public int $calls = 0;

        protected function createCompletion(array $payload): mixed
        {
            $this->calls++;

            throw new TransporterException(
                new \GuzzleHttp\Exception\ConnectException('Connection refused', new \GuzzleHttp\Psr7\Request('POST', 'test'))
            );
        }
    };

    expect(fn () => $completer->completeChat([['role' => 'user', 'content' => 'Say ok']]))
        ->toThrow(TransporterException::class)
        ->and($completer->calls)->toBe(2);
});
