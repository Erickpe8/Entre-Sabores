<?php

namespace Tests\Feature;

use Database\Seeders\CountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CountrySeeder::class);
    }

    public function test_health_returns_ok_when_checks_pass(): void
    {
        Config::set('monitoring.health_token', null);

        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => ['database', 'cache', 'queue_connection'],
            ]);

        foreach ($response->json('checks') as $passed) {
            $this->assertTrue($passed);
        }
    }

    public function test_health_forbidden_without_token_when_configured(): void
    {
        Config::set('monitoring.health_token', 'secret-health-token');

        $this->getJson('/health')->assertForbidden();
    }

    public function test_health_ok_with_query_token_when_configured(): void
    {
        Config::set('monitoring.health_token', 'secret-health-token');

        $this->getJson('/health?token=secret-health-token')->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_health_ok_with_header_token_when_configured(): void
    {
        Config::set('monitoring.health_token', 'secret-health-token');

        $this->withHeader('X-Health-Token', 'secret-health-token')
            ->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
