<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;

/**
 * Contadores por ventana de minuto (clave con TTL). Pensado para Redis.
 */
final class OperationalMetrics
{
    private const TTL_SECONDS = 7200;

    public static function incrementPostsCreated(): void
    {
        self::incr('posts_created');
    }

    public static function incrementHttpRequests(): void
    {
        self::incr('http_requests');
    }

    public static function incrementHttpErrors(): void
    {
        self::incr('http_errors');
    }

    public static function incrementBroadcastsEmitted(): void
    {
        self::incr('broadcasts_emitted');
    }

    private static function incr(string $suffix): void
    {
        if (! config('monitoring.metrics_enabled', true)) {
            return;
        }

        if (config('cache.default') !== 'redis') {
            return;
        }

        try {
            $connection = Redis::connection(config('cache.stores.redis.connection', 'cache'));
            $key = config('cache.prefix', '').'metrics:'.$suffix.':'.now()->format('YmdHi');
            $connection->incr($key);
            $connection->expire($key, self::TTL_SECONDS);
        } catch (\Throwable) {
            // No romper la petición si Redis cae.
        }
    }

    /**
     * @return array<string, int|string|null>
     */
    public static function snapshot(): array
    {
        $minute = now()->format('YmdHi');
        $prefix = (string) config('cache.prefix', '');

        if (config('cache.default') !== 'redis') {
            return [
                'driver' => config('cache.default'),
                'posts_created_last_minute' => null,
                'http_requests_last_minute' => null,
                'http_5xx_last_minute' => null,
                'broadcasts_emitted_last_minute' => null,
            ];
        }

        try {
            $connection = Redis::connection(config('cache.stores.redis.connection', 'cache'));

            return [
                'driver' => 'redis',
                'bucket' => $minute,
                'posts_created_last_minute' => (int) $connection->get($prefix.'metrics:posts_created:'.$minute),
                'http_requests_last_minute' => (int) $connection->get($prefix.'metrics:http_requests:'.$minute),
                'http_5xx_last_minute' => (int) $connection->get($prefix.'metrics:http_errors:'.$minute),
                'broadcasts_emitted_last_minute' => (int) $connection->get($prefix.'metrics:broadcasts_emitted:'.$minute),
            ];
        } catch (\Throwable) {
            return ['driver' => 'redis', 'error' => 'unavailable'];
        }
    }
}
