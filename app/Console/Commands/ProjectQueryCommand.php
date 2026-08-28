<?php

namespace App\Console\Commands;

use App\Support\ProjectIndex\Catalog;
use App\Support\ProjectIndex\Indexer;
use App\Support\ProjectIndex\IndexQuery;
use App\Support\ProjectIndex\IndexStore;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

class ProjectQueryCommand extends Command
{
    protected $signature = 'project:query
                            {action : overview|node|children|parent|dependencies|dependents|find|path|files|related|context|relevant|stale}
                            {id? : Node id}
                            {--q= : Search query}
                            {--depth=1 : Walk depth}
                            {--path= : Index directory (default .project)}
                            {--no-refresh : Do not auto-refresh stale index}';

    protected $description = 'Consulta el Project Intelligence Index (JSON en stdout)';

    public function handle(): int
    {
        $directory = $this->resolvedPath();
        $action = (string) $this->argument('action');
        $store = new IndexStore($directory);
        $indexer = Indexer::forApp($directory);

        try {
            if ($action === 'stale') {
                $report = $indexer->staleReport();
                $report['truth_rule'] = Catalog::TRUTH_RULE;
                $this->writeJson($report);

                return SymfonyCommand::SUCCESS;
            }

            if (! $this->option('no-refresh')) {
                $indexer->refreshIfStale();
            } elseif (! $store->hasIndex()) {
                $this->writeJson([
                    'error' => 'missing-index',
                    'truth_rule' => Catalog::TRUTH_RULE,
                ]);

                return SymfonyCommand::FAILURE;
            }

            $query = new IndexQuery($store);
            $payload = $query->run(
                $action,
                $this->argument('id'),
                $this->option('q') !== null ? (string) $this->option('q') : $this->argument('id'),
                (int) $this->option('depth'),
            );
            $this->writeJson($payload);

            return SymfonyCommand::SUCCESS;
        } catch (Throwable $exception) {
            $this->writeJson([
                'error' => $exception->getMessage(),
                'truth_rule' => Catalog::TRUTH_RULE,
            ]);

            return SymfonyCommand::FAILURE;
        }
    }

    private function resolvedPath(): string
    {
        $path = (string) $this->option('path');

        if ($path === '') {
            return base_path('.project');
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function writeJson(mixed $data): void
    {
        $this->line(rtrim(IndexStore::encode($data)));
    }
}
