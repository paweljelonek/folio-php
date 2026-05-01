<?php

declare(strict_types=1);

namespace App\Tests\Unit\Console;

use App\Console\AssetsLinkCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AssetsLinkCommandTest extends TestCase
{
    private string $tmpDir;
    private AssetsLinkCommand $command;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->tmpDir  = sys_get_temp_dir() . '/foliophp_test_' . uniqid();
        mkdir($this->tmpDir . '/source/css', 0755, true);
        mkdir($this->tmpDir . '/public',     0755, true);

        $this->command = new AssetsLinkCommand($this->tmpDir);
        $this->tester  = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    #[Test]
    public function createsRelativeSymlink(): void
    {
        $this->tester->execute(['--source' => 'source', '--target' => 'public/assets']);

        $link = $this->tmpDir . '/public/assets';
        $this->assertFileExists($link);
        $this->assertTrue(is_link($link));
        $this->assertSame('../source', readlink($link));
    }

    #[Test]
    public function symlinkPointsToCorrectDirectory(): void
    {
        $this->tester->execute(['--source' => 'source', '--target' => 'public/assets']);

        $this->assertDirectoryExists($this->tmpDir . '/public/assets/css');
    }

    #[Test]
    public function returnsSuccessExitCode(): void
    {
        $this->tester->execute(['--source' => 'source', '--target' => 'public/assets']);

        $this->assertSame(0, $this->tester->getStatusCode());
    }

    #[Test]
    public function failsWhenSourceDoesNotExist(): void
    {
        $this->tester->execute(['--source' => 'nonexistent', '--target' => 'public/assets']);

        $this->assertSame(1, $this->tester->getStatusCode());
        $this->assertStringContainsString('not found', $this->tester->getDisplay());
    }

    #[Test]
    public function warnsWhenSymlinkAlreadyExistsWithoutForce(): void
    {
        symlink($this->tmpDir . '/source', $this->tmpDir . '/public/assets');

        $this->tester->execute(['--source' => 'source', '--target' => 'public/assets']);

        $this->assertSame(0, $this->tester->getStatusCode());
        $this->assertStringContainsString('already exists', $this->tester->getDisplay());
    }

    #[Test]
    public function recreatesSymlinkWithForceFlag(): void
    {
        $oldSource = $this->tmpDir . '/old-source';
        mkdir($oldSource, 0755);
        symlink($oldSource, $this->tmpDir . '/public/assets');

        $this->tester->execute(['--source' => 'source', '--target' => 'public/assets', '--force' => true]);

        $this->assertSame(0, $this->tester->getStatusCode());
        $this->assertSame('../source', readlink($this->tmpDir . '/public/assets'));
    }

    #[Test]
    public function failsWhenTargetIsAnExistingDirectory(): void
    {
        mkdir($this->tmpDir . '/public/assets', 0755);

        $this->tester->execute(['--source' => 'source', '--target' => 'public/assets']);

        $this->assertSame(1, $this->tester->getStatusCode());
        $this->assertStringContainsString('not a symlink', $this->tester->getDisplay());
    }

    #[Test]
    public function createsParentDirectoryIfMissing(): void
    {
        $this->tester->execute(['--source' => 'source', '--target' => 'dist/nested/assets']);

        $this->assertTrue(is_link($this->tmpDir . '/dist/nested/assets'));
    }

    private function removeDir(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }
        if (is_link($path)) {
            unlink($path);
            return;
        }
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        ) as $item) {
            $item->isLink() ? unlink($item->getPathname()) : ($item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()));
        }
        rmdir($path);
    }
}
