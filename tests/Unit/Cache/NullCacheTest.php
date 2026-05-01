<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cache;

use App\Cache\NullCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullCacheTest extends TestCase
{
    private NullCache $cache;

    protected function setUp(): void
    {
        $this->cache = new NullCache();
    }

    #[Test]
    public function getAlwaysReturnsNull(): void
    {
        $this->cache->set('key', 'value');

        $this->assertNull($this->cache->get('key'));
    }

    #[Test]
    public function deleteDoesNotThrow(): void
    {
        $this->cache->delete('non-existent');

        $this->assertNull($this->cache->get('non-existent'));
    }

    #[Test]
    public function clearDoesNotThrow(): void
    {
        $this->cache->set('a', 'aaa');
        $this->cache->clear();

        $this->assertNull($this->cache->get('a'));
    }
}
