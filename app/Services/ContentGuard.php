<?php

namespace App\Services;

class ContentGuard
{
    /**
     * @var array<int, string>
     */
    private const PROMPT_INJECTION_PATTERNS = [
        '/\bignora(?:r)?\s+(?:todas?\s+)?(?:las?\s+)?instrucciones?\b/i',
        '/\bignore\s+all\s+(?:the\s+)?(?:previous\s+)?instructions?\b/i',
        '/\bact[uú]a\s+como\b/i',
        '/\bact\s+as\b/i',
        '/\bsystem\s+prompt\b/i',
        '/\bjailbreak\b/i',
    ];

    /**
     * Detección de groserías comunes (ES) con variantes mínimas disfrazadas.
     *
     * @var array<int, string>
     */
    private const PROFANITY_PATTERNS = [
        '/\bhij(?:o|4)s?\s*de\s*(?:pu(?:ta|t4)|p[\W_]*u[\W_]*t[\W_]*a)\b/iu',
        '/\bpu(?:ta|to|tas|tos|t4)\b/iu',
        '/\bp[\W_]*u[\W_]*t[\W_]*a\b/iu',
        '/\bmaric[oó]n(?:es)?\b/iu',
        '/\bcul(?:o|era|ero|ear|ead[oa]?)\b/iu',
        '/\bverga(?:s|zo)?\b/iu',
        '/\bpendej(?:o|a|os|as)\b/iu',
        '/\bcabr[oó]n(?:es)?\b/iu',
        '/\bgonorr(?:ea|hea)\b/iu',
        '/\bmalparid[oa]s?\b/iu',
        '/\bchinga(?:r|da|do|dos|das|te|ron)?\b/iu',
    ];

    /**
     * @return array{blocked: bool, reasons: array<int, string>}
     */
    public function inspectPostPayload(?string $title, ?string $description): array
    {
        $title = trim((string) $title);
        $description = trim((string) $description);
        $text = trim($title.' '.$description);

        $reasons = [];

        if ($text !== '' && $this->matchesAny(self::PROMPT_INJECTION_PATTERNS, $text)) {
            $reasons[] = 'prompt_injection';
        }

        if ($text !== '' && $this->matchesAny(self::PROFANITY_PATTERNS, $text)) {
            $reasons[] = 'profanity';
        }

        return [
            'blocked' => $reasons !== [],
            'reasons' => array_values(array_unique($reasons)),
        ];
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function matchesAny(array $patterns, string $text): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }
}

