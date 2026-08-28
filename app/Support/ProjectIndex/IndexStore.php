<?php

namespace App\Support\ProjectIndex;

final class IndexStore
{
    public function __construct(private readonly string $directory) {}

    public function directory(): string
    {
        return $this->directory;
    }

    public function path(string $name): string
    {
        return $this->directory.DIRECTORY_SEPARATOR.$name;
    }

    public function hasIndex(): bool
    {
        return is_file($this->path('metadata.json')) && is_file($this->path('index.json'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $name): ?array
    {
        $path = $this->path($name);

        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    public function write(string $name, mixed $data): void
    {
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }

        file_put_contents($this->path($name), self::encode($data));
    }

    public static function encode(mixed $data): string
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        return (is_string($json) ? $json : '{}')."\n";
    }
}
