<?php

declare(strict_types=1);

namespace App\Cache;

final class FilesystemCache implements CacheInterface
{
    public function __construct(
        private readonly string $cacheDir,
        private readonly int $ttl = 0,
    ) {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, recursive: true);
        }
    }

    public function get(string $key): ?string
    {
        $path = $this->htmlPath($key);

        if (!is_file($path)) {
            return null;
        }

        if ($this->ttl > 0 && time() > filemtime($path) + $this->ttl) {
            unlink($path);
            return null;
        }

        return file_get_contents($path) ?: null;
    }

    public function set(string $key, string $value): void
    {
        file_put_contents($this->htmlPath($key), $value, LOCK_EX);
    }

    public function delete(string $key): void
    {
        $path = $this->htmlPath($key);

        if (is_file($path)) {
            unlink($path);
        }
    }

    public function clear(): void
    {
        foreach (glob($this->cacheDir . '/*.html') ?: [] as $file) {
            unlink($file);
        }
    }

    private function htmlPath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.html';
    }
}
