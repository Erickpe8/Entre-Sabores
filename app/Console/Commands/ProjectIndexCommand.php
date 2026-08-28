<?php

namespace App\Console\Commands;

use App\Support\ProjectIndex\Catalog;
use App\Support\ProjectIndex\GitHookInstaller;
use App\Support\ProjectIndex\Indexer;
use App\Support\ProjectIndex\IndexStore;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ProjectIndexCommand extends Command
{
    protected $signature = 'project:index
                            {--full : Rebuild conceptual from catalog}
                            {--check : JSON stale report; exit 1 if stale}
                            {--if-stale : Refresh only when stale}
                            {--install-hooks : Copy .githooks to .git/hooks}
                            {--path= : Index directory (default .project)}';

    protected $description = 'Genera o verifica el Project Intelligence Index en .project/';

    public function handle(): int
    {
        $indexer = Indexer::forApp($this->resolvedPath());

        if ($this->option('install-hooks')) {
            (new GitHookInstaller)->install(base_path());
        }

        if ($this->option('check')) {
            $report = $indexer->staleReport();
            $report['truth_rule'] = Catalog::TRUTH_RULE;
            $this->writeJson($report);

            return $report['stale'] ? SymfonyCommand::FAILURE : SymfonyCommand::SUCCESS;
        }

        if ($this->option('if-stale')) {
            $result = $indexer->refreshIfStale();

            if ($result['refreshed']) {
                $this->line('Índice actualizado ('.$result['mode'].')');
            }

            return SymfonyCommand::SUCCESS;
        }

        $result = $indexer->build((bool) $this->option('full'));
        $this->writeJson([
            'ok' => true,
            'mode' => $result['mode'],
            'affected_nodes' => $result['affected_nodes'],
            'unmapped_app_php' => $result['unmapped_app_php'],
            'truth_rule' => Catalog::TRUTH_RULE,
        ]);

        return $result['unmapped_app_php'] === []
            ? SymfonyCommand::SUCCESS
            : SymfonyCommand::FAILURE;
    }

    private function resolvedPath(): string
    {
        $path = (string) $this->option('path');

        if ($path === '') {
            return base_path('.project');
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }

    private function writeJson(mixed $data): void
    {
        $this->line(rtrim(IndexStore::encode($data)));
    }
}
