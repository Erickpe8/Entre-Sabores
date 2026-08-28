<?php

namespace Tests\Feature\ProjectIndex;

use Tests\TestCase;

class ProjectIndexCliTest extends TestCase
{
    private string $indexDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indexDir = sys_get_temp_dir().'/es-idx-cli-'.bin2hex(random_bytes(4));
        mkdir($this->indexDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteTree($this->indexDir);
        parent::tearDown();
    }

    public function test_index_full_creates_artifacts_and_check_is_current(): void
    {
        $this->artisan('project:index', ['--full' => true, '--path' => $this->indexDir])
            ->assertSuccessful();

        foreach (['metadata.json', 'overview.json', 'index.json', 'graph.json', 'filemap.json'] as $name) {
            $this->assertFileExists($this->indexDir.'/'.$name);
        }

        $metadata = json_decode((string) file_get_contents($this->indexDir.'/metadata.json'), true);
        $this->assertSame(
            [],
            $metadata['unmapped_app_php'] ?? ['missing'],
            'Hay PHP de app/ sin nodo en el catálogo',
        );

        $this->artisan('project:index', ['--check' => true, '--path' => $this->indexDir])
            ->expectsOutputToContain('"stale": false')
            ->assertSuccessful();
    }

    public function test_relevant_finds_a_real_domain_feature_and_filemap_has_entrypoint(): void
    {
        $this->artisan('project:index', ['--full' => true, '--path' => $this->indexDir])
            ->assertSuccessful();

        $this->artisan('project:query', [
            'action' => 'relevant',
            '--q' => 'maridaje',
            '--path' => $this->indexDir,
            '--no-refresh' => true,
        ])
            ->expectsOutputToContain('ai.maridaje')
            ->assertSuccessful();

        $filemap = json_decode((string) file_get_contents($this->indexDir.'/filemap.json'), true);
        $this->assertContains(
            'app/Services/MaridajeAiAnalysisService.php',
            $filemap['ai.maridaje']['entrypoints'] ?? [],
        );
    }

    public function test_query_no_refresh_without_index_fails(): void
    {
        $empty = $this->indexDir.'/empty';
        mkdir($empty, 0777, true);

        $this->artisan('project:query', [
            'action' => 'overview',
            '--path' => $empty,
            '--no-refresh' => true,
        ])
            ->expectsOutputToContain('missing-index')
            ->assertFailed();
    }

    public function test_query_without_index_creates_and_responds(): void
    {
        $empty = $this->indexDir.'/create';
        mkdir($empty, 0777, true);

        $this->artisan('project:query', [
            'action' => 'overview',
            '--path' => $empty,
        ])
            ->expectsOutputToContain('"domains"')
            ->assertSuccessful();

        $this->assertFileExists($empty.'/index.json');
    }

    public function test_if_stale_when_current_does_not_print_updated(): void
    {
        $this->artisan('project:index', ['--full' => true, '--path' => $this->indexDir])
            ->assertSuccessful();

        $this->artisan('project:index', ['--if-stale' => true, '--path' => $this->indexDir])
            ->doesntExpectOutputToContain('actualizado')
            ->assertSuccessful();
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
