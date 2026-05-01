<?php

declare(strict_types=1);

namespace App\Cache;

final class NullCache implements CacheInterface
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function set(string $key, string $value): void {}

    public function delete(string $key): void {}

    public function clear(): void {}
}
