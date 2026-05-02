<?php

namespace App\Support;

/**
 * Expone solo claves conocidas del payload `data` de notificaciones en database.
 * Evita fugas si en el futuro se persisten campos internos en `toDatabase`.
 */
final class NotificationApiPayload
{
    /** @var list<string> */
    public const ALLOWED_KEYS = [
        'event',
        'title',
        'body',
        'post_id',
        'comment_id',
        'actor_id',
        'actor_username',
        'actor_name',
        'actor_avatar',
        'url',
        'kind',
    ];

    /**
     * @param  array<string, mixed>|string|null  $data
     * @return array<string, mixed>
     */
    public static function forApi(array|string|null $data): array
    {
        if ($data === null || $data === '') {
            return [];
        }

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        $out = [];
        foreach (self::ALLOWED_KEYS as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key];
            if ($value === null) {
                continue;
            }
            $out[$key] = match ($key) {
                'post_id', 'comment_id', 'actor_id' => is_numeric($value) ? (int) $value : $value,
                default => $value,
            };
        }

        return $out;
    }
}
