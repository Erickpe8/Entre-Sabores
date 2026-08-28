<?php

namespace Tests\Unit\ProjectIndex;

use App\Support\ProjectIndex\Catalog;
use App\Support\ProjectIndex\GitHookInstaller;
use App\Support\ProjectIndex\Indexer;
use App\Support\ProjectIndex\IndexQuery;
use App\Support\ProjectIndex\IndexStore;
use App\Support\ProjectIndex\PhpImportScanner;
use Tests\TestCase;

class ProjectIndexEngineTest extends TestCase
{
    private string $root;

    private string $indexDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/es-idx-'.bin2hex(random_bytes(4));
        $this->indexDir = $this->root.'/idx';
        mkdir($this->root.'/app/Demo', 0777, true);
        mkdir($this->root.'/tests/Feature', 0777, true);
        mkdir($this->indexDir, 0777, true);

        file_put_contents($this->root.'/app/Demo/Leaf.php', <<<'PHP'
<?php
namespace App\Demo;
use App\Demo\GroupedA;
use App\Demo\{
    GroupedB as AliasB,
};
class Leaf {}
PHP);

        file_put_contents($this->root.'/app/Demo/GroupedA.php', "<?php\nnamespace App\\Demo;\nclass GroupedA {}\n");
        file_put_contents($this->root.'/app/Demo/GroupedB.php', "<?php\nnamespace App\\Demo;\nclass GroupedB {}\n");
        file_put_contents($this->root.'/tests/Feature/LeafTest.php', "<?php\nclass LeafTest {}\n");
    }

    protected function tearDown(): void
    {
        $this->deleteTree($this->root);
        parent::tearDown();
    }

    public function test_catalog_ids_parents_and_depends_on_are_valid(): void
    {
        $ids = [];

        foreach (Catalog::definition()['nodes'] as $node) {
            $id = (string) $node['id'];
            $this->assertNotSame('', $id);
            $this->assertArrayNotHasKey($id, $ids);
            $ids[$id] = true;
        }

        foreach (Catalog::definition()['nodes'] as $node) {
            $parent = $node['parent'] ?? null;

            if (is_string($parent) && $parent !== '') {
                $this->assertArrayHasKey($parent, $ids, $node['id']);
            }

            foreach ($node['depends_on'] ?? [] as $dep) {
                $this->assertArrayHasKey((string) $dep, $ids, $node['id'].'→'.$dep);
            }
        }
    }

    public function test_scanner_resolves_simple_and_grouped_imports(): void
    {
        $scanner = new PhpImportScanner($this->root);
        $imports = $scanner->scan((string) file_get_contents($this->root.'/app/Demo/Leaf.php'));

        $this->assertContains('app/Demo/GroupedA.php', $imports);
        $this->assertContains('app/Demo/GroupedB.php', $imports);
    }

    public function test_build_filemap_uses_edge_and_catalog_depends_on(): void
    {
        $indexer = $this->makeIndexer();
        $indexer->build(true);

        $store = new IndexStore($this->indexDir);
        $filemap = $store->read('filemap.json');
        $graph = $store->read('graph.json');
        $this->assertIsArray($filemap);
        $this->assertArrayHasKey('demo.leaf', $filemap);
        $this->assertContains('app/Demo/Leaf.php', $filemap['demo.leaf']['entrypoints']);

        $types = [];

        foreach ($graph['edges'] as $edge) {
            $types[$edge['type']][] = $edge;
        }

        $this->assertNotEmpty($types['depends_on'] ?? []);
        $catalogEdge = collect($types['depends_on'])->first(
            fn (array $edge): bool => $edge['from'] === 'demo.leaf' && $edge['to'] === 'demo.shared',
        );
        $this->assertNotNull($catalogEdge);
        $this->assertFalse($catalogEdge['inferred']);
        $this->assertSame('catalog', $catalogEdge['source']);

        $uses = collect($types['uses'] ?? [])->first(
            fn (array $edge): bool => $edge['from'] === 'demo.leaf' && $edge['to'] === 'demo.shared',
        );
        $this->assertNotNull($uses);
        $this->assertTrue($uses['inferred']);
        $this->assertSame('import-analysis', $uses['source']);
    }

    public function test_incremental_rebuild_reports_only_affected_nodes(): void
    {
        $indexer = $this->makeIndexer();
        $indexer->build(true);

        file_put_contents($this->root.'/app/Demo/Leaf.php', (string) file_get_contents($this->root.'/app/Demo/Leaf.php')."// touch\n");

        $result = $indexer->build(false);

        $this->assertSame('incremental', $result['mode']);
        $this->assertContains('demo.leaf', $result['affected_nodes']);
        $this->assertNotContains('demo.shared', $result['affected_nodes']);
    }

    public function test_relevant_finds_the_feature_node(): void
    {
        $indexer = $this->makeIndexer();
        $indexer->build(true);

        $payload = (new IndexQuery(new IndexStore($this->indexDir)))->run('relevant', query: 'leaf feature');

        $ids = array_column($payload['nodes'], 'id');
        $this->assertContains('demo.leaf', $ids);
        $this->assertLessThanOrEqual(5, count($payload['nodes']));
        $this->assertArrayHasKey('truth_rule', $payload);
        $this->assertNotEmpty($payload['nodes'][0]['entrypoints']);
    }

    public function test_stale_detects_source_file_changes(): void
    {
        $indexer = $this->makeIndexer();
        $indexer->build(true);

        $this->assertFalse($indexer->staleReport()['stale']);

        file_put_contents($this->root.'/app/Demo/GroupedA.php', (string) file_get_contents($this->root.'/app/Demo/GroupedA.php')."// x\n");

        $report = $indexer->staleReport();
        $this->assertTrue($report['stale']);
        $this->assertContains('source-files-changed', $report['reasons']);
    }

    public function test_refresh_if_stale_skips_when_current_and_rebuilds_when_file_changed(): void
    {
        $indexer = $this->makeIndexer();
        $indexer->build(true);

        $this->assertSame(['refreshed' => false, 'mode' => 'current'], $indexer->refreshIfStale());

        file_put_contents($this->root.'/app/Demo/GroupedB.php', (string) file_get_contents($this->root.'/app/Demo/GroupedB.php')."// y\n");

        $result = $indexer->refreshIfStale();
        $this->assertTrue($result['refreshed']);
        $this->assertSame('incremental', $result['mode']);
    }

    public function test_git_hook_installer_copies_hooks_into_temp_git_dir(): void
    {
        mkdir($this->root.'/.githooks', 0777, true);
        mkdir($this->root.'/.git/hooks', 0777, true);
        file_put_contents($this->root.'/.githooks/post-commit', "#!/bin/sh\nexit 0\n");

        $installed = (new GitHookInstaller)->install($this->root);

        $this->assertNotEmpty($installed);
        $this->assertFileExists($this->root.'/.git/hooks/post-commit');
        $this->assertSame("#!/bin/sh\nexit 0\n", file_get_contents($this->root.'/.git/hooks/post-commit'));
    }

    private function makeIndexer(): Indexer
    {
        return new Indexer(
            new IndexStore($this->indexDir),
            new PhpImportScanner($this->root),
            $this->root,
            $this->miniCatalog(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function miniCatalog(): array
    {
        return [
            'project' => ['id' => 'demo', 'name' => 'Demo', 'summary' => 'Fixture temporal.'],
            'scan_roots' => ['app', 'tests'],
            'ignore' => ['vendor', 'node_modules'],
            'nodes' => [
                [
                    'id' => 'demo',
                    'type' => 'project',
                    'name' => 'Demo',
                    'parent' => null,
                    'summary' => 'Raíz fixture.',
                    'status' => 'implemented',
                    'paths' => [],
                    'entrypoints' => [],
                    'depends_on' => [],
                ],
                [
                    'id' => 'domain.demo',
                    'type' => 'domain',
                    'name' => 'Demo domain',
                    'parent' => 'demo',
                    'summary' => 'Dominio fixture.',
                    'status' => 'implemented',
                    'paths' => ['app/Demo/'],
                    'entrypoints' => [],
                    'depends_on' => [],
                ],
                [
                    'id' => 'demo.leaf',
                    'type' => 'feature',
                    'name' => 'Leaf feature',
                    'parent' => 'domain.demo',
                    'summary' => 'Feature hoja del fixture.',
                    'status' => 'implemented',
                    'aliases' => ['leaf'],
                    'paths' => ['app/Demo/Leaf.php', 'tests/Feature/LeafTest.php'],
                    'entrypoints' => ['app/Demo/Leaf.php'],
                    'depends_on' => ['demo.shared'],
                ],
                [
                    'id' => 'demo.shared',
                    'type' => 'service',
                    'name' => 'Shared demo',
                    'parent' => 'demo.leaf',
                    'summary' => 'Servicio compartido del fixture.',
                    'status' => 'implemented',
                    'paths' => ['app/Demo/GroupedA.php', 'app/Demo/GroupedB.php'],
                    'entrypoints' => ['app/Demo/GroupedA.php'],
                    'depends_on' => [],
                ],
            ],
        ];
    }

    private function deleteTree(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($directory);
    }
}
