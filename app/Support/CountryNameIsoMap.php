<?php

namespace App\Support;

/**
 * ISO 3166-1 alpha-2 para etiquetas de país del catálogo (nombre exacto del seeder).
 */
final class CountryNameIsoMap
{
    /**
     * @return non-empty-string|null
     */
    public static function isoForCountryName(string $name): ?string
    {
        return match ($name) {
            'Colombia' => 'CO',
            'México' => 'MX',
            'Argentina' => 'AR',
            'Perú' => 'PE',
            'España' => 'ES',
            'Italia' => 'IT',
            'Japón' => 'JP',
            'Francia' => 'FR',
            'Estados Unidos' => 'US',
            'Brasil' => 'BR',
            'Chile' => 'CL',
            'Ecuador' => 'EC',
            'Venezuela' => 'VE',
            'Uruguay' => 'UY',
            'Paraguay' => 'PY',
            'Bolivia' => 'BO',
            'Costa Rica' => 'CR',
            'Cuba' => 'CU',
            'Panamá' => 'PA',
            'Puerto Rico' => 'PR',
            'República Dominicana' => 'DO',
            'Guatemala' => 'GT',
            'Honduras' => 'HN',
            'El Salvador' => 'SV',
            'Nicaragua' => 'NI',
            'Canadá' => 'CA',
            'Reino Unido' => 'GB',
            'Alemania' => 'DE',
            'Grecia' => 'GR',
            'Portugal' => 'PT',
            'Suiza' => 'CH',
            'Austria' => 'AT',
            'Bélgica' => 'BE',
            'Países Bajos' => 'NL',
            'Irlanda' => 'IE',
            'Noruega' => 'NO',
            'Suecia' => 'SE',
            'Dinamarca' => 'DK',
            'Finlandia' => 'FI',
            'Polonia' => 'PL',
            'Rusia' => 'RU',
            'Ucrania' => 'UA',
            'Tailandia' => 'TH',
            'Vietnam' => 'VN',
            'China' => 'CN',
            'India' => 'IN',
            'Corea del Sur' => 'KR',
            'Singapur' => 'SG',
            'Australia' => 'AU',
            'Nueva Zelanda' => 'NZ',
            'Marruecos' => 'MA',
            'Turquía' => 'TR',
            'Israel' => 'IL',
            'Líbano' => 'LB',
            'Sudáfrica' => 'ZA',
            'Egipto' => 'EG',
            'Nigeria' => 'NG',
            'Kenia' => 'KE',
            'Islandia' => 'IS',
            'Chipre' => 'CY',
            'Malta' => 'MT',
            'Eslovenia' => 'SI',
            'Eslovaquia' => 'SK',
            'Chequia' => 'CZ',
            'Hungría' => 'HU',
            'Rumanía' => 'RO',
            'Bulgaria' => 'BG',
            'Croacia' => 'HR',
            'Serbia' => 'RS',
            default => null,
        };
    }
}
