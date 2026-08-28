<?php

namespace App\Support\ProjectIndex;

final class PhpImportScanner
{
    /**
     * @var list<string>
     */
    private const IGNORED_PREFIXES = [
        'Illuminate\\',
        'Symfony\\',
        'Carbon\\',
        'GuzzleHttp\\',
        'Intervention\\',
        'League\\',
        'Opcodes\\',
        'Predis\\',
        'Spatie\\',
        'PHPUnit\\',
        'Pest\\',
        'Mockery\\',
        'Faker\\',
        'Livewire\\',
        'Database\\',
        'Tests\\',
        'Psr\\',
        'Monolog\\',
        'NunoMaduro\\',
        'PhpOffice\\',
        'Aws\\',
        'Pusher\\',
    ];

    public function __construct(private readonly string $repoRoot) {}

    /**
     * @return list<string> Rutas relativas PSR-4 que existen en el repo.
     */
    public function scan(string $source): array
    {
        if (! preg_match_all('/^use\s+(function\s+|const\s+)?([^;]+);/m', $source, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $paths = [];

        foreach ($matches as $match) {
            if (trim((string) ($match[1] ?? '')) !== '') {
                continue;
            }

            foreach ($this->expandClause(trim($match[2])) as $fqcn) {
                $relative = $this->fqcnToExistingPath($fqcn);

                if ($relative !== null) {
                    $paths[] = $relative;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * @return list<string>
     */
    private function expandClause(string $clause): array
    {
        if (preg_match('/^(.+?)\{(.+)\}$/s', $clause, $grouped) === 1) {
            $prefix = rtrim(trim($grouped[1]), '\\').'\\';
            $parts = preg_split('/\s*,\s*/', trim($grouped[2])) ?: [];
            $names = [];

            foreach ($parts as $part) {
                $part = trim($part, " \t\n\r\0\x0B,");

                if ($part === '') {
                    continue;
                }

                $names[] = $prefix.$this->stripAlias($part);
            }

            return $names;
        }

        return [$this->stripAlias($clause)];
    }

    private function stripAlias(string $name): string
    {
        $name = trim($name);

        if (preg_match('/^(.+?)\s+as\s+\S+$/i', $name, $alias) === 1) {
            return trim($alias[1]);
        }

        return $name;
    }

    private function fqcnToExistingPath(string $fqcn): ?string
    {
        $fqcn = ltrim(str_replace('/', '\\', $fqcn), '\\');

        if (! str_starts_with($fqcn, 'App\\')) {
            return null;
        }

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if (str_starts_with($fqcn, $prefix)) {
                return null;
            }
        }

        $relative = 'app/'.str_replace('\\', '/', substr($fqcn, 4)).'.php';
        $absolute = $this->repoRoot.'/'.str_replace('/', DIRECTORY_SEPARATOR, $relative);

        if (! is_file($absolute)) {
            return null;
        }

        return $relative;
    }
}
