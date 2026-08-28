<?php

namespace App\Support\ProjectIndex;

final class Indexer
{
    public const INDEXABLE_EXTENSIONS = [
        'php', 'js', 'ts', 'css', 'md', 'mdc', 'json', 'yml', 'yaml', 'xml', 'conf', 'sh',
    ];

    /**
     * @param  array{
     *     project: array{id: string, name: string, summary: string},
     *     scan_roots: list<string>,
     *     ignore: list<string>,
     *     nodes: list<array<string, mixed>>
     * }  $catalog
     */
    public function __construct(
        private readonly IndexStore $store,
        private readonly PhpImportScanner $scanner,
        private readonly string $repoRoot,
        private readonly array $catalog,
    ) {}

    public static function forApp(?string $indexDirectory = null): self
    {
        $root = base_path();
        $dir = $indexDirectory ?? $root.DIRECTORY_SEPARATOR.'.project';

        return new self(
            new IndexStore($dir),
            new PhpImportScanner($root),
            $root,
            Catalog::definition(),
        );
    }

    /**
     * @return array{stale: bool, reasons: list<string>, warnings: list<string>, files_hash: string, catalog_hash: string}
     */
    public function staleReport(): array
    {
        $files = $this->scanFiles();
        $filesHash = $this->filesHash($files);
        $catalogHash = $this->catalogHash();
        $warnings = $this->gitWarnings();
        $reasons = [];

        if (! $this->store->hasIndex()) {
            $reasons[] = 'missing-index';
        } else {
            $metadata = $this->store->read('metadata.json') ?? [];

            if (($metadata['catalog_hash'] ?? '') !== $catalogHash) {
                $reasons[] = 'catalog-changed';
            }

            if (($metadata['files_hash'] ?? '') !== $filesHash) {
                $reasons[] = 'source-files-changed';
            }
        }

        return [
            'stale' => $reasons !== [],
            'reasons' => $reasons,
            'warnings' => $warnings,
            'files_hash' => $filesHash,
            'catalog_hash' => $catalogHash,
        ];
    }

    /**
     * @return array{refreshed: bool, mode: string}
     */
    public function refreshIfStale(): array
    {
        $report = $this->staleReport();

        if (! $report['stale']) {
            return ['refreshed' => false, 'mode' => 'current'];
        }

        $full = in_array('missing-index', $report['reasons'], true)
            || in_array('catalog-changed', $report['reasons'], true);

        $this->build($full);

        return [
            'refreshed' => true,
            'mode' => $full ? 'full' : 'incremental',
        ];
    }

    /**
     * @return array{mode: string, affected_nodes: list<string>, unmapped_app_php: list<string>}
     */
    public function build(bool $full = false): array
    {
        $this->assertCatalogIntegrity();

        $files = $this->scanFiles();
        $filesHash = $this->filesHash($files);
        $catalogHash = $this->catalogHash();
        $previous = $this->store->read('fingerprints.json') ?? ['files' => []];
        $prevFiles = is_array($previous['files'] ?? null) ? $previous['files'] : [];

        $mustReparseAll = $full
            || ($previous['catalog_hash'] ?? '') !== $catalogHash
            || $prevFiles === [];

        $fingerprints = [];
        $changedPaths = [];

        foreach ($files as $relative => $hash) {
            $previousEntry = $prevFiles[$relative] ?? null;
            $reuse = ! $mustReparseAll
                && is_array($previousEntry)
                && ($previousEntry['hash'] ?? null) === $hash;

            if ($reuse) {
                $fingerprints[$relative] = [
                    'hash' => $hash,
                    'imports' => array_values($previousEntry['imports'] ?? []),
                ];

                continue;
            }

            $changedPaths[] = $relative;
            $fingerprints[$relative] = [
                'hash' => $hash,
                'imports' => str_ends_with($relative, '.php')
                    ? $this->scanner->scan((string) file_get_contents($this->absolute($relative)))
                    : [],
            ];
        }

        $nodesById = [];

        foreach ($this->catalog['nodes'] as $node) {
            $nodesById[(string) $node['id']] = $node;
        }

        $affected = $this->affectedNodeIds($nodesById, $changedPaths);

        $index = $this->buildIndex($nodesById);
        $filemap = $this->buildFilemap($nodesById, array_keys($files));
        $graph = $this->buildGraph($nodesById, $fingerprints, $filemap);
        $overview = $this->buildOverview($nodesById);
        $physical = $this->buildPhysical($nodesById, array_keys($files));
        $index['physical'] = $physical;

        $unmapped = $this->unmappedAppPhp($nodesById, array_keys($files));
        $git = $this->gitState();
        $warnings = $this->gitWarnings();

        if ($unmapped !== []) {
            $warnings[] = 'unmapped-app-php:'.count($unmapped);
        }

        $this->store->write('fingerprints.json', [
            'catalog_hash' => $catalogHash,
            'files_hash' => $filesHash,
            'files' => $fingerprints,
        ]);

        $this->store->write('metadata.json', [
            'schema_version' => Catalog::SCHEMA_VERSION,
            'generated_at' => gmdate('c'),
            'commit' => $git['commit'],
            'dirty' => $git['dirty'],
            'files_hash' => $filesHash,
            'catalog_hash' => $catalogHash,
            'mode' => $mustReparseAll ? 'full' : 'incremental',
            'node_count' => count($nodesById),
            'edge_count' => count($graph['edges'] ?? []),
            'truth_rule' => Catalog::TRUTH_RULE,
            'warnings' => $warnings,
            'unmapped_app_php' => $unmapped,
        ]);

        $this->store->write('overview.json', $overview);
        $this->store->write('index.json', $index);
        $this->store->write('graph.json', $graph);
        $this->store->write('filemap.json', $filemap);

        return [
            'mode' => $mustReparseAll ? 'full' : 'incremental',
            'affected_nodes' => $affected,
            'unmapped_app_php' => $unmapped,
        ];
    }

    /**
     * @return array<string, string> relative path => sha1
     */
    public function scanFiles(): array
    {
        $found = [];
        $ignore = $this->catalog['ignore'] ?? [];

        foreach ($this->catalog['scan_roots'] as $root) {
            $absolute = $this->absolute($root);

            if (is_file($absolute)) {
                $relative = $this->normalize($root);

                if (! $this->isIgnored($relative, $ignore) && $this->isIndexable($relative)) {
                    $found[$relative] = sha1_file($absolute) ?: '';
                }

                continue;
            }

            if (! is_dir($absolute)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolute, \FilesystemIterator::SKIP_DOTS),
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $relative = $this->normalize(substr($file->getPathname(), strlen($this->repoRoot) + 1));

                if ($this->isIgnored($relative, $ignore) || ! $this->isIndexable($relative)) {
                    continue;
                }

                $found[$relative] = sha1_file($file->getPathname()) ?: '';
            }
        }

        ksort($found);

        return $found;
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  list<string>  $allFiles
     * @return list<string>
     */
    public function unmappedAppPhp(array $nodesById, array $allFiles): array
    {
        $unmapped = [];

        foreach ($allFiles as $file) {
            if (! str_starts_with($file, 'app/') || ! str_ends_with($file, '.php')) {
                continue;
            }

            $matched = false;

            foreach ($nodesById as $node) {
                if ($this->fileMatchesNode($file, $node)) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                $unmapped[] = $file;
            }
        }

        sort($unmapped);

        return $unmapped;
    }

    private function catalogHash(): string
    {
        $encoded = json_encode($this->catalog, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return sha1(is_string($encoded) ? $encoded : '');
    }

    /**
     * @param  array<string, string>  $files
     */
    private function filesHash(array $files): string
    {
        $chunks = [];

        foreach ($files as $path => $hash) {
            $chunks[] = $path.':'.$hash;
        }

        return sha1(implode("\n", $chunks));
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @return array<string, mixed>
     */
    private function buildIndex(array $nodesById): array
    {
        $children = [];

        foreach ($nodesById as $node) {
            $parent = $node['parent'] ?? null;

            if (! is_string($parent) || $parent === '') {
                continue;
            }

            $children[$parent][] = (string) $node['id'];
        }

        $compact = [];

        foreach ($nodesById as $id => $node) {
            $item = [
                'id' => $id,
                'type' => $node['type'],
                'name' => $node['name'],
                'summary' => $node['summary'],
                'status' => $node['status'] ?? 'implemented',
                'parent' => $node['parent'],
                'children' => $children[$id] ?? [],
                'depends_on' => array_values($node['depends_on'] ?? []),
                'entrypoints' => array_values($node['entrypoints'] ?? []),
            ];

            if (! empty($node['aliases'])) {
                $item['aliases'] = array_values($node['aliases']);
            }

            $compact[$id] = $item;
        }

        return [
            'truth_rule' => Catalog::TRUTH_RULE,
            'nodes' => $compact,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @return array<string, mixed>
     */
    private function buildOverview(array $nodesById): array
    {
        $projectId = (string) ($this->catalog['project']['id'] ?? 'entre-sabores');
        $domains = [];

        foreach ($nodesById as $node) {
            if (($node['type'] ?? '') !== 'domain' || ($node['parent'] ?? null) !== $projectId) {
                continue;
            }

            $children = [];

            foreach ($nodesById as $child) {
                if (($child['parent'] ?? null) !== $node['id']) {
                    continue;
                }

                $children[] = [
                    'id' => $child['id'],
                    'type' => $child['type'],
                    'name' => $child['name'],
                    'summary' => $child['summary'],
                ];
            }

            $domains[] = [
                'id' => $node['id'],
                'type' => 'domain',
                'name' => $node['name'],
                'summary' => $node['summary'],
                'children' => $children,
            ];
        }

        return [
            'project' => $this->catalog['project'] + ['truth_rule' => Catalog::TRUTH_RULE],
            'truth_rule' => Catalog::TRUTH_RULE,
            'context_budget' => '2–5 nodos. Leer entrypoints. Ampliar solo si la tarea es compleja. Validar siempre en código.',
            'how_to_query' => 'php artisan project:query relevant --q="<tarea>"',
            'domains' => $domains,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  list<string>  $allFiles
     * @return array<string, array{entrypoints: list<string>, dirs: list<string>, files: list<string>, tests: list<string>}>
     */
    private function buildFilemap(array $nodesById, array $allFiles): array
    {
        $map = [];

        foreach ($nodesById as $id => $node) {
            $type = (string) ($node['type'] ?? '');

            if (in_array($type, ['project', 'domain'], true)) {
                continue;
            }

            $matched = [];

            foreach ($allFiles as $file) {
                if ($this->fileMatchesNode($file, $node)) {
                    $matched[] = $file;
                }
            }

            $entrypoints = [];

            foreach ($node['entrypoints'] ?? [] as $entry) {
                $entry = $this->normalize((string) $entry);

                if (in_array($entry, $allFiles, true) || is_file($this->absolute($entry))) {
                    $entrypoints[] = $entry;
                }
            }

            $collapsed = $this->collapseFileList($matched, $type === 'test' ? 8 : 12);

            if ($entrypoints === [] && $collapsed['files'] === [] && $collapsed['dirs'] === [] && $collapsed['tests'] === []) {
                continue;
            }

            $map[$id] = [
                'entrypoints' => array_values(array_unique($entrypoints)),
                'dirs' => $collapsed['dirs'],
                'files' => $collapsed['files'],
                'tests' => $collapsed['tests'],
            ];
        }

        return $map;
    }

    /**
     * @param  list<string>  $files
     * @return array{dirs: list<string>, files: list<string>, tests: list<string>}
     */
    private function collapseFileList(array $files, int $threshold): array
    {
        $tests = [];
        $code = [];

        foreach ($files as $file) {
            if (str_starts_with($file, 'tests/')) {
                $tests[] = $file;
            } else {
                $code[] = $file;
            }
        }

        $codeCollapsed = $this->collapseGroup($code, $threshold);
        $testCollapsed = $this->collapseGroup($tests, 8);

        return [
            'dirs' => array_values(array_unique(array_merge(
                $codeCollapsed['dirs'],
                $testCollapsed['dirs'],
            ))),
            'files' => $codeCollapsed['files'],
            'tests' => $testCollapsed['files'],
        ];
    }

    /**
     * @param  list<string>  $files
     * @return array{dirs: list<string>, files: list<string>}
     */
    private function collapseGroup(array $files, int $threshold): array
    {
        $byDir = [];

        foreach ($files as $file) {
            $dir = dirname($file);
            $dir = $dir === '.' ? '' : $dir.'/';
            $byDir[$dir][] = $file;
        }

        $dirs = [];
        $kept = [];

        foreach ($byDir as $dir => $group) {
            if ($dir !== '' && count($group) > $threshold) {
                $dirs[] = $dir;
            } else {
                foreach ($group as $file) {
                    $kept[] = $file;
                }
            }
        }

        sort($dirs);
        sort($kept);

        return ['dirs' => $dirs, 'files' => $kept];
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  array<string, array{hash: string, imports: list<string>}>  $fingerprints
     * @param  array<string, mixed>  $filemap
     * @return array{edges: list<array<string, mixed>>}
     */
    private function buildGraph(array $nodesById, array $fingerprints, array $filemap): array
    {
        $edges = [];
        $edgeIndex = [];
        $primary = $this->primaryNodeByFile($nodesById, array_keys($fingerprints));

        foreach ($nodesById as $id => $node) {
            foreach ($node['depends_on'] ?? [] as $target) {
                $target = (string) $target;

                if (! isset($nodesById[$target])) {
                    continue;
                }

                $key = $id.'>'.$target.'>depends_on';
                $edges[] = [
                    'from' => $id,
                    'to' => $target,
                    'type' => 'depends_on',
                    'confidence' => 1,
                    'source' => 'catalog',
                    'inferred' => false,
                    'weight' => 1,
                ];
                $edgeIndex[$key] = count($edges) - 1;
            }
        }

        foreach ($fingerprints as $file => $payload) {
            $fromId = $primary[$file] ?? null;

            if ($fromId === null) {
                continue;
            }

            foreach ($payload['imports'] as $imported) {
                $toId = $primary[$imported] ?? null;

                if ($toId === null || $toId === $fromId) {
                    continue;
                }

                $key = $fromId.'>'.$toId.'>uses';

                if (isset($edgeIndex[$key])) {
                    $edges[$edgeIndex[$key]]['weight']++;

                    continue;
                }

                $edges[] = [
                    'from' => $fromId,
                    'to' => $toId,
                    'type' => 'uses',
                    'confidence' => 0.85,
                    'source' => 'import-analysis',
                    'inferred' => true,
                    'weight' => 1,
                ];
                $edgeIndex[$key] = count($edges) - 1;
            }
        }

        if (isset($nodesById['tests.suite'])) {
            foreach ($nodesById as $id => $node) {
                if (($node['type'] ?? '') !== 'feature') {
                    continue;
                }

                $tests = $filemap[$id]['tests'] ?? [];
                $hasTestDir = false;

                foreach ($filemap[$id]['dirs'] ?? [] as $dir) {
                    if (str_starts_with((string) $dir, 'tests/')) {
                        $hasTestDir = true;
                        break;
                    }
                }

                if ($tests === [] && ! $hasTestDir) {
                    continue;
                }

                $edges[] = [
                    'from' => 'tests.suite',
                    'to' => $id,
                    'type' => 'tests',
                    'confidence' => 0.9,
                    'source' => 'path-map',
                    'inferred' => true,
                    'weight' => 1,
                ];
            }
        }

        return ['edges' => $edges];
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  list<string>  $allFiles
     * @return array<string, list<string>>
     */
    private function buildPhysical(array $nodesById, array $allFiles): array
    {
        $physical = [];

        foreach ($allFiles as $file) {
            $dir = dirname($file);
            $dir = $dir === '.' ? $file : $dir.'/';
            $ids = [];

            foreach ($nodesById as $id => $node) {
                if ($this->fileMatchesNode($file, $node)) {
                    $ids[] = $id;
                }
            }

            if ($ids === []) {
                continue;
            }

            $physical[$dir] = array_values(array_unique(array_merge($physical[$dir] ?? [], $ids)));
        }

        ksort($physical);

        return $physical;
    }

    /**
     * Un nodo primario por archivo para aristas `uses`.
     *
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  list<string>  $files
     * @return array<string, string>
     */
    public function primaryNodeByFile(array $nodesById, array $files): array
    {
        $skip = ['project', 'domain', 'test', 'documentation', 'configuration', 'infrastructure'];
        $map = [];

        foreach ($files as $file) {
            $bestId = null;
            $bestLength = -1;
            $bestType = null;

            foreach ($nodesById as $id => $node) {
                $type = (string) ($node['type'] ?? '');

                if (in_array($type, $skip, true) || ! $this->fileMatchesNode($file, $node)) {
                    continue;
                }

                $length = $this->longestMatchingPatternLength($file, $node);

                if ($length > $bestLength || ($length === $bestLength && $type === 'service' && $bestType !== 'service')) {
                    $bestId = $id;
                    $bestLength = $length;
                    $bestType = $type;
                }
            }

            if ($bestId !== null) {
                $map[$file] = $bestId;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    public function fileMatchesNode(string $file, array $node): bool
    {
        $file = $this->normalize($file);

        foreach ($node['paths'] ?? [] as $pattern) {
            if ($this->pathMatches($file, $this->normalize((string) $pattern))) {
                return true;
            }
        }

        foreach ($node['entrypoints'] ?? [] as $entry) {
            if ($file === $this->normalize((string) $entry)) {
                return true;
            }
        }

        return false;
    }

    public function pathMatches(string $file, string $pattern): bool
    {
        $file = $this->normalize($file);
        $pattern = $this->normalize($pattern);

        if ($pattern === '') {
            return false;
        }

        if (str_contains($pattern, '*')) {
            return fnmatch($pattern, $file, FNM_PATHNAME | FNM_NOESCAPE);
        }

        if (str_ends_with($pattern, '/')) {
            return $file === rtrim($pattern, '/') || str_starts_with($file, $pattern);
        }

        return $file === $pattern || str_starts_with($file, $pattern.'/');
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function longestMatchingPatternLength(string $file, array $node): int
    {
        $best = 0;
        $file = $this->normalize($file);

        foreach (array_merge($node['paths'] ?? [], $node['entrypoints'] ?? []) as $pattern) {
            $pattern = $this->normalize((string) $pattern);

            if ($this->pathMatches($file, $pattern)) {
                $best = max($best, strlen($pattern));
            }
        }

        return $best;
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  list<string>  $changedPaths
     * @return list<string>
     */
    private function affectedNodeIds(array $nodesById, array $changedPaths): array
    {
        $ids = [];

        foreach ($changedPaths as $path) {
            foreach ($nodesById as $id => $node) {
                if ($this->fileMatchesNode($path, $node)) {
                    $ids[$id] = true;
                }
            }
        }

        $list = array_keys($ids);
        sort($list);

        return $list;
    }

    private function assertCatalogIntegrity(): void
    {
        $ids = [];
        $errors = [];

        foreach ($this->catalog['nodes'] as $node) {
            $id = (string) ($node['id'] ?? '');

            if ($id === '' || isset($ids[$id])) {
                $errors[] = 'duplicate-or-empty-id:'.$id;
            }

            $ids[$id] = true;
        }

        foreach ($this->catalog['nodes'] as $node) {
            $parent = $node['parent'] ?? null;

            if (is_string($parent) && $parent !== '' && ! isset($ids[$parent])) {
                $errors[] = 'missing-parent:'.$node['id'].'→'.$parent;
            }

            foreach ($node['depends_on'] ?? [] as $dep) {
                if (! isset($ids[(string) $dep])) {
                    $errors[] = 'invalid-depends_on:'.$node['id'].'→'.$dep;
                }
            }
        }

        if ($errors !== []) {
            throw new \RuntimeException('Catálogo inválido: '.implode('; ', $errors));
        }
    }

    /**
     * @param  list<string>  $ignore
     */
    private function isIgnored(string $relative, array $ignore): bool
    {
        foreach ($ignore as $pattern) {
            $pattern = $this->normalize((string) $pattern);

            if ($relative === $pattern || str_starts_with($relative, rtrim($pattern, '/').'/')) {
                return true;
            }

            $first = explode('/', $relative)[0];

            if ($first === rtrim($pattern, '/')) {
                return true;
            }
        }

        return false;
    }

    private function isIndexable(string $relative): bool
    {
        if (str_ends_with($relative, '.blade.php')) {
            return true;
        }

        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));

        return in_array($ext, self::INDEXABLE_EXTENSIONS, true);
    }

    private function absolute(string $relative): string
    {
        return $this->repoRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->normalize($relative));
    }

    private function normalize(string $path): string
    {
        return str_replace('\\', '/', ltrim($path, '/'));
    }

    /**
     * @return array{commit: ?string, dirty: bool}
     */
    private function gitState(): array
    {
        $commit = $this->gitOutput('rev-parse HEAD');
        $status = $this->gitOutput('status --porcelain');

        return [
            'commit' => $commit !== '' ? $commit : null,
            'dirty' => $status !== '',
        ];
    }

    /**
     * @return list<string>
     */
    private function gitWarnings(): array
    {
        $warnings = [];
        $git = $this->gitState();
        $metadata = $this->store->read('metadata.json') ?? [];

        if ($git['dirty']) {
            $warnings[] = 'working-tree-dirty';
        }

        $previousCommit = $metadata['commit'] ?? null;

        if (is_string($previousCommit) && $git['commit'] !== null && $previousCommit !== $git['commit']) {
            $warnings[] = 'commit-changed';
        }

        return $warnings;
    }

    private function gitOutput(string $args): string
    {
        $redirect = DIRECTORY_SEPARATOR === '\\' ? ' 2>NUL' : ' 2>/dev/null';
        $command = 'git -C '.escapeshellarg($this->repoRoot).' '.$args.$redirect;
        $lines = [];
        $code = 0;
        exec($command, $lines, $code);

        if ($code !== 0) {
            return '';
        }

        return trim(implode("\n", $lines));
    }
}
