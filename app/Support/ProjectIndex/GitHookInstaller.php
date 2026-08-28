<?php

namespace App\Support\ProjectIndex;

final class GitHookInstaller
{
    /**
     * Copia .githooks → .git/hooks. No ejecuta git config.
     *
     * @return list<string> Rutas de hooks instalados.
     */
    public function install(string $repoRoot): array
    {
        $source = $repoRoot.DIRECTORY_SEPARATOR.'.githooks';
        $gitDir = $repoRoot.DIRECTORY_SEPARATOR.'.git';
        $target = $gitDir.DIRECTORY_SEPARATOR.'hooks';

        if (! is_dir($source) || ! is_dir($gitDir)) {
            return [];
        }

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $installed = [];
        $files = scandir($source) ?: [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $from = $source.DIRECTORY_SEPARATOR.$file;

            if (! is_file($from)) {
                continue;
            }

            $to = $target.DIRECTORY_SEPARATOR.$file;
            copy($from, $to);
            @chmod($to, 0755);
            $installed[] = str_replace('\\', '/', $to);
        }

        return $installed;
    }
}
