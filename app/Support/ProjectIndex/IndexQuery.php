<?php

namespace App\Support\ProjectIndex;

final class IndexQuery
{
    /**
     * @var list<string>
     */
    private const STOPWORDS = [
        'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'de', 'del', 'al',
        'y', 'o', 'u', 'a', 'en', 'con', 'por', 'para', 'que', 'se', 'su', 'sus',
        'es', 'son', 'como', 'mas', 'lo', 'le', 'les', 'este', 'esta', 'estos',
        'estas', 'the', 'and', 'for', 'with', 'from', 'into', 'that', 'this',
        'are', 'was', 'were', 'not',
    ];

    public function __construct(private readonly IndexStore $store) {}

    /**
     * @return array<string, mixed>
     */
    public function run(string $action, ?string $id = null, ?string $query = null, int $depth = 1): array
    {
        return match ($action) {
            'overview' => $this->must('overview.json'),
            'stale' => throw new \LogicException('stale se resuelve en el comando con Indexer::staleReport()'),
            'node' => $this->node($this->requireId($id)),
            'children' => $this->children($this->requireId($id)),
            'parent' => $this->parent($this->requireId($id)),
            'dependencies' => $this->walk($this->requireId($id), 'depends_on', max(1, $depth)),
            'dependents' => $this->dependents($this->requireId($id), max(1, $depth)),
            'find' => $this->find((string) $query),
            'path' => $this->path((string) ($query ?? $id)),
            'files' => $this->files($this->requireId($id)),
            'related' => $this->related($this->requireId($id)),
            'context' => $this->context($this->requireId($id)),
            'relevant' => $this->relevant((string) $query),
            default => throw new \InvalidArgumentException('Acción no soportada: '.$action),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function node(string $id): array
    {
        $nodes = $this->nodes();

        if (! isset($nodes[$id])) {
            throw new \InvalidArgumentException('Nodo no encontrado: '.$id);
        }

        return $nodes[$id] + ['truth_rule' => Catalog::TRUTH_RULE];
    }

    /**
     * @return array<string, mixed>
     */
    private function children(string $id): array
    {
        $node = $this->node($id);

        return [
            'id' => $id,
            'children' => array_map(fn (string $childId): array => $this->summaryOf($childId), $node['children'] ?? []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parent(string $id): array
    {
        $node = $this->node($id);
        $parentId = $node['parent'] ?? null;

        return [
            'id' => $id,
            'parent' => is_string($parentId) ? $this->summaryOf($parentId) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function walk(string $id, string $edgeType, int $depth): array
    {
        $nodes = $this->nodes();
        $edges = $this->edges();
        $visited = [];
        $found = [];

        $stack = [[$id, 0]];

        while ($stack !== []) {
            [$current, $level] = array_pop($stack);

            if (isset($visited[$current]) || $level >= $depth) {
                if ($current !== $id && isset($nodes[$current])) {
                    $found[$current] = $this->summaryOf($current) + ['depth' => $level];
                }

                continue;
            }

            $visited[$current] = true;

            if ($current !== $id) {
                $found[$current] = $this->summaryOf($current) + ['depth' => $level];
            }

            foreach ($edges as $edge) {
                $type = (string) ($edge['type'] ?? '');

                if (($edge['from'] ?? '') !== $current) {
                    continue;
                }

                $allowed = $edgeType === '*'
                    || $type === $edgeType
                    || ($edgeType === 'depends_on' && in_array($type, ['depends_on', 'uses'], true));

                if (! $allowed) {
                    continue;
                }

                $to = (string) ($edge['to'] ?? '');

                if ($to !== '' && ! isset($visited[$to])) {
                    $stack[] = [$to, $level + 1];
                }
            }
        }

        return [
            'id' => $id,
            'depth' => $depth,
            'nodes' => array_values($found),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dependents(string $id, int $depth): array
    {
        $edges = $this->edges();
        $visited = [];
        $found = [];
        $stack = [[$id, 0]];

        while ($stack !== []) {
            [$current, $level] = array_pop($stack);

            if (isset($visited[$current]) || $level > $depth) {
                continue;
            }

            $visited[$current] = true;

            if ($current !== $id) {
                $found[] = $this->summaryOf($current) + ['depth' => $level];
            }

            if ($level >= $depth) {
                continue;
            }

            foreach ($edges as $edge) {
                if (($edge['to'] ?? '') !== $current) {
                    continue;
                }

                $from = (string) ($edge['from'] ?? '');

                if ($from !== '' && ! isset($visited[$from])) {
                    $stack[] = [$from, $level + 1];
                }
            }
        }

        return [
            'id' => $id,
            'depth' => $depth,
            'nodes' => $found,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function find(?string $query): array
    {
        $tokens = $this->tokens((string) $query);
        $hits = [];

        foreach ($this->nodes() as $id => $node) {
            $haystack = $this->haystack($node);
            $score = 0;

            foreach ($tokens as $token) {
                if (str_contains($this->normalize((string) $id), $token)) {
                    $score += 5;
                } elseif (str_contains($this->normalize((string) ($node['name'] ?? '')), $token)) {
                    $score += 4;
                } elseif (str_contains($this->normalize(implode(' ', $node['aliases'] ?? [])), $token)) {
                    $score += 4;
                } elseif (str_contains($haystack, $token)) {
                    $score += 1;
                }
            }

            if ($score > 0) {
                $hits[] = $this->summaryOf($id) + ['score' => $score];
            }
        }

        usort($hits, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return ['q' => $query, 'nodes' => array_slice($hits, 0, 15)];
    }

    /**
     * @return array<string, mixed>
     */
    private function path(string $file): array
    {
        $file = str_replace('\\', '/', $file);
        $filemap = $this->must('filemap.json');
        $index = $this->nodes();
        $matches = [];

        foreach ($filemap as $id => $map) {
            $all = array_merge($map['entrypoints'] ?? [], $map['files'] ?? [], $map['tests'] ?? [], $map['dirs'] ?? []);

            foreach ($all as $candidate) {
                $candidate = str_replace('\\', '/', (string) $candidate);

                if ($candidate === $file || (str_ends_with($candidate, '/') && str_starts_with($file, $candidate))) {
                    $matches[] = $this->summaryOf((string) $id);
                    break;
                }
            }
        }

        if ($matches === []) {
            foreach ($index as $id => $node) {
                foreach ($node['entrypoints'] ?? [] as $entry) {
                    if (str_replace('\\', '/', (string) $entry) === $file) {
                        $matches[] = $this->summaryOf($id);
                    }
                }
            }
        }

        return ['path' => $file, 'nodes' => $matches];
    }

    /**
     * @return array<string, mixed>
     */
    private function files(string $id): array
    {
        $filemap = $this->must('filemap.json');

        return [
            'id' => $id,
            'filemap' => $filemap[$id] ?? ['entrypoints' => [], 'dirs' => [], 'files' => [], 'tests' => []],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function related(string $id): array
    {
        return [
            'id' => $id,
            'node' => $this->summaryOf($id),
            'dependencies' => $this->walk($id, 'depends_on', 1)['nodes'],
            'dependents' => $this->dependents($id, 1)['nodes'],
            'children' => $this->children($id)['children'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function context(string $id): array
    {
        $node = $this->node($id);
        $parentId = $node['parent'] ?? null;

        return [
            'truth_rule' => Catalog::TRUTH_RULE,
            'node' => $node,
            'parent' => is_string($parentId) ? $this->summaryOf($parentId) : null,
            'children' => $this->children($id)['children'],
            'dependencies' => $this->walk($id, 'depends_on', 1)['nodes'],
            'dependents' => $this->dependents($id, 1)['nodes'],
            'files' => $this->files($id)['filemap'],
            'next_step' => 'Leer los entrypoints y validar en el código. El índice no es fuente de verdad.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function relevant(string $query): array
    {
        $tokens = $this->tokens($query);
        $filemap = $this->store->read('filemap.json') ?? [];
        $hits = [];

        foreach ($this->nodes() as $id => $node) {
            $type = (string) ($node['type'] ?? '');

            if (in_array($type, ['project', 'domain'], true)) {
                continue;
            }

            $score = 0;
            $haystack = $this->haystack($node);

            foreach ($tokens as $token) {
                if (str_contains($this->normalize((string) $id), $token)) {
                    $score += 5;
                } elseif (str_contains($this->normalize((string) ($node['name'] ?? '')), $token)) {
                    $score += 4;
                } elseif (str_contains($this->normalize(implode(' ', $node['aliases'] ?? [])), $token)) {
                    $score += 4;
                } elseif (str_contains($haystack, $token)) {
                    $score += 1;
                }
            }

            if ($score <= 0) {
                continue;
            }

            $map = $filemap[$id] ?? [];

            $hits[] = [
                'id' => $id,
                'name' => $node['name'],
                'summary' => $node['summary'],
                'score' => $score,
                'entrypoints' => array_values($node['entrypoints'] ?? []),
                'depends_on' => array_values($node['depends_on'] ?? []),
                'suggested_files' => array_slice(array_values(array_unique(array_merge(
                    $node['entrypoints'] ?? [],
                    $map['files'] ?? [],
                ))), 0, 8),
            ];
        }

        usort($hits, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return [
            'truth_rule' => Catalog::TRUTH_RULE,
            'q' => $query,
            'nodes' => array_slice($hits, 0, 5),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryOf(string $id): array
    {
        $node = $this->nodes()[$id] ?? null;

        if ($node === null) {
            return ['id' => $id];
        }

        return [
            'id' => $id,
            'type' => $node['type'] ?? null,
            'name' => $node['name'] ?? null,
            'summary' => $node['summary'] ?? null,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function nodes(): array
    {
        $index = $this->must('index.json');
        $nodes = $index['nodes'] ?? [];

        if (! is_array($nodes)) {
            return [];
        }

        return $nodes;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function edges(): array
    {
        $graph = $this->store->read('graph.json') ?? [];
        $edges = $graph['edges'] ?? [];

        return is_array($edges) ? $edges : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function must(string $name): array
    {
        $data = $this->store->read($name);

        if ($data === null) {
            throw new \RuntimeException('Índice ausente: '.$name);
        }

        return $data;
    }

    private function requireId(?string $id): string
    {
        if ($id === null || $id === '') {
            throw new \InvalidArgumentException('Esta acción requiere {id}.');
        }

        return $id;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function haystack(array $node): string
    {
        return $this->normalize(implode(' ', [
            $node['id'] ?? '',
            $node['name'] ?? '',
            $node['summary'] ?? '',
            implode(' ', $node['aliases'] ?? []),
            implode(' ', $node['entrypoints'] ?? []),
        ]));
    }

    /**
     * @return list<string>
     */
    private function tokens(string $query): array
    {
        $normalized = $this->normalize($query);
        $parts = preg_split('/[^a-z0-9]+/', $normalized) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            if (strlen($part) < 3 || in_array($part, self::STOPWORDS, true)) {
                continue;
            }

            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        ];

        return strtr($value, $map);
    }
}
