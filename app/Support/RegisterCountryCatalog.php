<?php

namespace App\Support;

/**
 * Países permitidos en el registro y resolución ISO ↔ nombre.
 */
final class RegisterCountryCatalog
{
    /** @var list<string> */
    public const NAMES = [
        'Colombia',
        'México',
        'Argentina',
        'Chile',
        'Perú',
        'Ecuador',
        'Venezuela',
        'Bolivia',
        'Paraguay',
        'Uruguay',
    ];

    /**
     * @return non-empty-string|null
     */
    public static function nameForIso(string $iso): ?string
    {
        $iso = strtoupper(trim($iso));
        if ($iso === '' || $iso === 'XX' || $iso === 'T1') {
            return null;
        }

        foreach (self::NAMES as $name) {
            if (CountryNameIsoMap::isoForCountryName($name) === $iso) {
                return $name;
            }
        }

        return null;
    }

    public static function isAllowedName(string $name): bool
    {
        return in_array($name, self::NAMES, true);
    }
}
