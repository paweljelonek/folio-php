<?php

declare(strict_types=1);

namespace App\Cache;

final class CacheFactory
{
    public function create(string $driver, array $options = []): CacheInterface
    {
        return match ($driver) {
            'file' => new FilesystemCache(
                cacheDir: $options['cache_dir'] ?? sys_get_temp_dir() . '/foliophp_cache',
                ttl:      (int) ($options['ttl'] ?? 0),
            ),
            'null' => new NullCache(),
            default => throw new \InvalidArgumentException(
                sprintf('Unknown cache driver "%s". Supported: file, null.', $driver)
            ),
        };
    }
}
