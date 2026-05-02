<?php

namespace Tests\Unit;

use App\Support\NotificationApiPayload;
use PHPUnit\Framework\TestCase;

class NotificationApiPayloadTest extends TestCase
{
    public function test_strips_unknown_keys(): void
    {
        $payload = NotificationApiPayload::forApi([
            'event' => 'like',
            'title' => 'Hola',
            'internal_debug' => 'no exponer',
            'post_id' => '42',
        ]);

        $this->assertSame([
            'event' => 'like',
            'title' => 'Hola',
            'post_id' => 42,
        ], $payload);
    }

    public function test_handles_json_string(): void
    {
        $json = '{"event":"follow","title":"X","extra":"y"}';
        $payload = NotificationApiPayload::forApi($json);

        $this->assertSame(['event' => 'follow', 'title' => 'X'], $payload);
    }
}
