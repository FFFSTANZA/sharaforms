<?php

use App\Service\FormImport\FormImportException;
use App\Service\FormImport\FormImporterInterface;
use App\Service\FormImport\FormImportRegistry;
use App\Service\FormImport\FormImportService;
use App\Service\FormImport\Importers\AbstractImporter;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

describe('FormImportRegistry', function () {
    it('lists all supported sources', function () {
        $registry = new FormImportRegistry();

        expect($registry->sources())->toBe(['typeform', 'tally', 'fillout', 'google_forms']);
    });

    it('resolves each registered source to an importer instance', function () {
        $registry = new FormImportRegistry();

        foreach ($registry->sources() as $source) {
            expect($registry->resolve($source))->toBeInstanceOf(FormImporterInterface::class);
        }
    });

    it('returns human-readable labels for known sources', function () {
        $registry = new FormImportRegistry();

        expect($registry->label('typeform'))->toBe('Typeform');
        expect($registry->label('tally'))->toBe('Tally');
        expect($registry->label('fillout'))->toBe('Fillout');
        expect($registry->label('google_forms'))->toBe('Google Forms');
    });

    it('falls back to the source key for unknown labels', function () {
        $registry = new FormImportRegistry();

        expect($registry->label('notion'))->toBe('notion');
    });

    it('throws InvalidArgumentException for unknown sources', function () {
        $registry = new FormImportRegistry();

        expect(fn () => $registry->resolve('notion'))
            ->toThrow(InvalidArgumentException::class, 'Unknown import source: notion');
    });
});

describe('FormImportService', function () {
    it('delegates to the resolved importer after validation', function () {
        $importer = Mockery::mock(FormImporterInterface::class);
        $importer->shouldReceive('validate')->once()->andReturn(true);
        $importer->shouldReceive('import')->once()->with(['url' => 'https://example.com/form'])->andReturn(['title' => 'Imported']);

        $registry = Mockery::mock(FormImportRegistry::class);
        $registry->shouldReceive('resolve')->once()->with('typeform')->andReturn($importer);

        $service = new FormImportService($registry);

        expect($service->import('typeform', ['url' => 'https://example.com/form']))
            ->toBe(['title' => 'Imported']);
    });

    it('throws FormImportException when validation fails', function () {
        $importer = Mockery::mock(FormImporterInterface::class);
        $importer->shouldReceive('validate')->once()->andReturn(false);

        $registry = Mockery::mock(FormImportRegistry::class);
        $registry->shouldReceive('resolve')->once()->with('typeform')->andReturn($importer);
        $registry->shouldReceive('label')->once()->with('typeform')->andReturn('Typeform');

        $service = new FormImportService($registry);

        expect(fn () => $service->import('typeform', ['url' => 'not-a-url']))
            ->toThrow(FormImportException::class, 'Invalid import data for source: Typeform');
    });
});

describe('AbstractImporter', function () {
    $makeImporter = function () {
        return new class extends AbstractImporter {
            public function allowedDomains(): array
            {
                return ['*.typeform.com'];
            }

            public function import(array $importData): array
            {
                return ['imported' => true];
            }

            public function exposedIsDomainAllowed(string $url): bool
            {
                return $this->isDomainAllowed($url);
            }

            public function exposedFetchHtml(string $url): string
            {
                return $this->fetchHtml($url);
            }

            public function exposedExtractNextData(string $html): array
            {
                return $this->extractNextData($html);
            }

            public function exposedSanitizeText(?string $text, int $maxLength = 255): string
            {
                return $this->sanitizeText($text, $maxLength);
            }
        };
    };

    it('validates a URL on an allowed domain', function () use ($makeImporter) {
        $importer = $makeImporter();

        expect($importer->validate(['url' => 'https://example.typeform.com/to/abc123']))->toBeTrue();
    });

    it('rejects URLs on disallowed domains', function () use ($makeImporter) {
        $importer = $makeImporter();

        expect($importer->validate(['url' => 'https://notion.so/form/abc']))->toBeFalse();
    });

    it('rejects malformed or missing URLs', function () use ($makeImporter) {
        $importer = $makeImporter();

        expect($importer->validate(['url' => 'not a url']))->toBeFalse();
        expect($importer->validate(['url' => 'https://']))->toBeFalse();
        expect($importer->validate([]))->toBeFalse();
    });

    it('matches wildcard domains for subdomains and the apex domain', function () use ($makeImporter) {
        $importer = $makeImporter();

        expect($importer->exposedIsDomainAllowed('https://example.typeform.com/to/abc'))->toBeTrue();
        expect($importer->exposedIsDomainAllowed('https://deep.sub.typeform.com/to/abc'))->toBeTrue();
        expect($importer->exposedIsDomainAllowed('https://typeform.com/to/abc'))->toBeTrue();
        expect($importer->exposedIsDomainAllowed('https://notypeform.com/to/abc'))->toBeFalse();
    });

    it('fetches HTML with a successful response', function () use ($makeImporter) {
        Http::fake([
            'example.com/*' => Http::response('<html>fixture</html>', 200),
        ]);

        $importer = $makeImporter();

        expect($importer->exposedFetchHtml('https://example.com/form'))->toBe('<html>fixture</html>');
    });

    it('throws FormImportException on a failed HTTP response', function () use ($makeImporter) {
        Http::fake([
            'example.com/*' => Http::response('Not Found', 404),
        ]);

        $importer = $makeImporter();

        expect(fn () => $importer->exposedFetchHtml('https://example.com/missing'))
            ->toThrow(FormImportException::class, 'Failed to fetch form page. HTTP status: 404');
    });

    it('throws FormImportException when the response exceeds 5 MB', function () use ($makeImporter) {
        Http::fake([
            'example.com/*' => Http::response(str_repeat('x', 5 * 1024 * 1024 + 10), 200),
        ]);

        $importer = $makeImporter();

        expect(fn () => $importer->exposedFetchHtml('https://example.com/huge'))
            ->toThrow(FormImportException::class, 'Response too large (> 5 MB).');
    });

    it('extracts __NEXT_DATA__ JSON from the page', function () use ($makeImporter) {
        $html = '<html><body><script id="__NEXT_DATA__" type="application/json">{"form":{"id":1}}</script></body></html>';

        $importer = $makeImporter();

        expect($importer->exposedExtractNextData($html))->toBe(['form' => ['id' => 1]]);
    });

    it('throws FormImportException when __NEXT_DATA__ is missing', function () use ($makeImporter) {
        $importer = $makeImporter();

        expect(fn () => $importer->exposedExtractNextData('<html><body>no data</body></html>'))
            ->toThrow(FormImportException::class, 'Could not find form data in the page.');
    });

    it('throws FormImportException on malformed __NEXT_DATA__ JSON', function () use ($makeImporter) {
        $html = '<script id="__NEXT_DATA__" type="application/json">{not json</script>';

        $importer = $makeImporter();

        expect(fn () => $importer->exposedExtractNextData($html))
            ->toThrow(FormImportException::class, 'Failed to parse form data from page.');
    });

    it('sanitizes text by trimming, stripping tags and truncating', function () use ($makeImporter) {
        $importer = $makeImporter();

        expect($importer->exposedSanitizeText(null))->toBe('');
        expect($importer->exposedSanitizeText('  <b>Hello</b> world  '))->toBe('Hello world');
        expect($importer->exposedSanitizeText('abcdefghij', 5))->toBe('abcde');
    });
});
