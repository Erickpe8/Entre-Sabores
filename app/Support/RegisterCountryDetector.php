<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class RegisterCountryDetector
{
    /**
     * @return array{country: string, iso: string, source: string}|null
     */
    public function hintFromRequest(Request $request): ?array
    {
        $iso = $request->header('CF-IPCountry')
            ?? $request->header('X-Vercel-IP-Country')
            ?? $request->header('x-vercel-ip-country');

        if (! is_string($iso) || $iso === '') {
            return null;
        }

        $country = RegisterCountryCatalog::nameForIso($iso);
        if ($country === null) {
            return null;
        }

        return [
            'country' => $country,
            'iso' => strtoupper($iso),
            'source' => 'ip',
        ];
    }

    /**
     * @return array{country: string, iso: string, source: string, city: string|null}|null
     */
    public function resolveCoordinates(float $latitude, float $longitude): ?array
    {
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'localityLanguage' => 'es',
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();
        $iso = is_string($payload['countryCode'] ?? null) ? $payload['countryCode'] : null;
        if ($iso === null) {
            return null;
        }

        $country = RegisterCountryCatalog::nameForIso($iso);
        if ($country === null) {
            return null;
        }

        $city = $payload['city'] ?? $payload['locality'] ?? null;

        return [
            'country' => $country,
            'iso' => strtoupper($iso),
            'source' => 'gps',
            'city' => is_string($city) && $city !== '' ? $city : null,
        ];
    }
}
