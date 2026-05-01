<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cache;

use App\Cache\FilesystemCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilesystemCacheTest extends TestCase
{
    private FilesystemCache $cache;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/foliophp_test_' . uniqid();
        $this->cache    = new FilesystemCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
    }

    #[Test]
    public function storesAndRetrievesHtml(): void
    {
        $html = '<h1>Hello</h1><p>World</p>';

        $this->cache->set('page:/', $html);

        $this->assertSame($html, $this->cache->get('page:/'));
    }

    #[Test]
    public function storesHtmlAsReadableFile(): void
    {
        $html = '<h1>Hello</h1>';
        $this->cache->set('page:/about', $html);

        $htmlFiles = glob($this->cacheDir . '/*.html');

        $this->assertCount(1, $htmlFiles);
        $this->assertSame($html, file_get_contents($htmlFiles[0]));
    }

    #[Test]
    public function returnsNullForMissingKey(): void
    {
        $this->assertNull($this->cache->get('non-existent'));
    }

    #[Test]
    public function deletesHtmlFile(): void
    {
        $this->cache->set('page:/about', '<html>about</html>');
        $this->cache->delete('page:/about');

        $this->assertNull($this->cache->get('page:/about'));
        $this->assertEmpty(glob($this->cacheDir . '/*.html'));
    }

    #[Test]
    public function deleteNonExistentKeyDoesNotThrow(): void
    {
        $this->cache->delete('does-not-exist');

        $this->assertNull($this->cache->get('does-not-exist'));
    }

    #[Test]
    public function clearRemovesAllHtmlFiles(): void
    {
        $this->cache->set('page:/', '<html>home</html>');
        $this->cache->set('page:/about', '<html>about</html>');

        $this->cache->clear();

        $this->assertNull($this->cache->get('page:/'));
        $this->assertNull($this->cache->get('page:/about'));
        $this->assertEmpty(glob($this->cacheDir . '/*.html'));
    }

    #[Test]
    public function expiredEntryReturnsNullAndRemovesFile(): void
    {
        $cache = new FilesystemCache($this->cacheDir, ttl: 1);
        $cache->set('page:/ttl', '<html>ttl</html>');

        sleep(2);

        $this->assertNull($cache->get('page:/ttl'));
        $this->assertEmpty(glob($this->cacheDir . '/*.html'));
    }

    #[Test]
    public function zeroTtlNeverExpires(): void
    {
        $cache = new FilesystemCache($this->cacheDir, ttl: 0);
        $cache->set('page:/forever', '<html>forever</html>');

        $this->assertSame('<html>forever</html>', $cache->get('page:/forever'));
    }

    #[Test]
    public function createsCacheDirIfNotExists(): void
    {
        $newDir = sys_get_temp_dir() . '/foliophp_new_' . uniqid();
        $this->assertDirectoryDoesNotExist($newDir);

        $cache = new FilesystemCache($newDir);
        $cache->set('k', '<p>v</p>');

        $this->assertDirectoryExists($newDir);

        foreach (glob($newDir . '/*') ?: [] as $f) {
            unlink($f);
        }
        rmdir($newDir);
    }
}
