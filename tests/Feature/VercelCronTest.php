<?php

namespace Tests\Feature;

use Database\Seeders\CountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class VercelCronTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CountrySeeder::class);
    }

    public function test_schedule_cron_forbidden_without_bearer_token(): void
    {
        Config::set('monitoring.cron_secret', 'cron-secret');

        $this->get('/internal/cron/schedule')->assertForbidden();
    }

    public function test_schedule_cron_runs_with_valid_bearer_token(): void
    {
        Config::set('monitoring.cron_secret', 'cron-secret');

        $this->withToken('cron-secret')
            ->getJson('/internal/cron/schedule')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('command', 'schedule:run');
    }

    public function test_queue_cron_service_unavailable_without_secret(): void
    {
        Config::set('monitoring.cron_secret', null);

        $this->get('/internal/cron/queue')->assertStatus(503);
    }
}
