<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidReCaptcha;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidReCaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.re_captcha.site_key', 'test-site-key');
        config()->set('services.re_captcha.secret_key', 'test-secret-key');
        config()->set('services.re_captcha.project_id', 'test-project');
    }

    private function assertCaptcha(string $token, $fakeResponse): bool
    {
        Http::fake([
            ValidReCaptcha::RECAPTCHA_API_BASE . '/*' => $fakeResponse,
        ]);

        $v = Validator::make(
            ['g-recaptcha-response' => $token],
            ['g-recaptcha-response' => [new ValidReCaptcha()]]
        );

        return $v->passes();
    }

    /** @test */
    public function it_rejects_empty_response(): void
    {
        $this->assertFalse($this->assertCaptcha('', Http::response([])));
    }

    /** @test */
    public function it_passes_with_valid_assessment(): void
    {
        $this->assertTrue($this->assertCaptcha('valid-token', Http::response([
            'tokenProperties' => ['valid' => true],
            'riskAnalysis' => ['score' => 0.9],
        ])));
    }

    /** @test */
    public function it_fails_with_invalid_assessment(): void
    {
        $this->assertFalse($this->assertCaptcha('bad-token', Http::response([
            'tokenProperties' => ['valid' => false],
            'riskAnalysis' => ['score' => 0.1],
        ])));
    }

    /** @test */
    public function it_fails_open_on_http_429_quota_exhausted(): void
    {
        $this->assertTrue($this->assertCaptcha('quota-token', Http::response([
            'error' => [
                'code' => 429,
                'message' => 'Quota exceeded for quota metric',
                'status' => 'RESOURCE_EXHAUSTED',
            ],
        ], 429)));
    }

    /** @test */
    public function it_fails_open_when_score_threshold_is_met(): void
    {
        config()->set('services.re_captcha.score_threshold', 0.5);

        $this->assertTrue($this->assertCaptcha('score-token', Http::response([
            'tokenProperties' => ['valid' => true],
            'riskAnalysis' => ['score' => 0.7],
        ])));
    }

    /** @test */
    public function it_fails_when_score_is_below_threshold(): void
    {
        config()->set('services.re_captcha.score_threshold', 0.5);

        $this->assertFalse($this->assertCaptcha('low-score-token', Http::response([
            'tokenProperties' => ['valid' => true],
            'riskAnalysis' => ['score' => 0.2],
        ])));
    }

    /** @test */
    public function it_fails_when_keys_are_missing(): void
    {
        config()->set('services.re_captcha.secret_key', null);

        $this->assertFalse($this->assertCaptcha('token', Http::response([])));
    }
}
