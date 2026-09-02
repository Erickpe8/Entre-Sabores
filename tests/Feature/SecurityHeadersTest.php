<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_routes_include_baseline_security_headers(): void
    {
        $response = $this->get(route('welcome'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_production_https_responses_include_hsts_and_csp(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $response = $this->withServerVariables(['HTTPS' => 'on'])
            ->get(route('welcome'));

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
    }
}
