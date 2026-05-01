<?php

declare(strict_types=1);

namespace App\Tests\Unit\Content;

use App\Content\ContentNotFoundException;
use App\Content\FrontMatterParser;
use App\Content\MarkdownContentLoader;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MarkdownContentLoaderTest extends TestCase
{
    private MarkdownContentLoader $loader;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->loader      = new MarkdownContentLoader(new FrontMatterParser());
        $this->fixturesDir = STUBS_DIR . '/content';
    }

    #[Test]
    public function loadsPageFromValidMarkdownFile(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertSame('Home', $page->getTitle());
        $this->assertSame('default', $page->getTemplate());
        $this->assertStringContainsString('<h1>', $page->getBody());
        $this->assertStringContainsString('Welcome', $page->getBody());
    }

    #[Test]
    public function convertsMarkdownToHtml(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertStringContainsString('<h1>', $page->getBody());
        $this->assertStringNotContainsString('# Welcome', $page->getBody());
    }

    #[Test]
    public function exposesSynopsis(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertSame('Home page synopsis', $page->getSynopsis());
    }

    #[Test]
    public function returnsEmptyStringWhenSlugMissing(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertSame('', $page->getSlug());
    }

    #[Test]
    public function readsSlugFromFrontMatter(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'md_test_');
        file_put_contents($tmpFile, "---\ntitle: Test\nslug: my-custom-slug\n---\nbody");

        try {
            $this->assertSame('my-custom-slug', $this->loader->load($tmpFile)->getSlug());
        } finally {
            unlink($tmpFile);
        }
    }

    #[Test]
    public function exposesImage(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertSame('home.png', $page->getImage());
    }

    #[Test]
    public function parsesPublishDateToDateTimeImmutable(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertInstanceOf(DateTimeImmutable::class, $page->getPublishDate());
        $this->assertSame('28.04.2026', $page->getPublishDate()->format('d.m.Y'));
    }

    #[Test]
    public function exposesCategories(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertSame(['General'], $page->getCategories());
    }

    #[Test]
    public function exposesTags(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');

        $this->assertSame(['Welcome'], $page->getTags());
    }

    #[Test]
    public function throwsContentNotFoundExceptionForMissingFile(): void
    {
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessageMatches('/not found/i');

        $this->loader->load($this->fixturesDir . '/does-not-exist.md');
    }

    #[Test]
    public function defaultsTemplateToDefaultWhenMissingInFrontMatter(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'md_test_');
        file_put_contents($tmpFile, "---\ntitle: No Template\n---\nbody");

        try {
            $page = $this->loader->load($tmpFile);
            $this->assertSame('default', $page->getTemplate());
        } finally {
            unlink($tmpFile);
        }
    }

    #[Test]
    public function publishDateIsNullForInvalidFormat(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'md_test_');
        file_put_contents($tmpFile, "---\ntitle: Bad Date\npublish_date: 2026-01-28\n---\nbody");

        try {
            $this->assertNull($this->loader->load($tmpFile)->getPublishDate());
        } finally {
            unlink($tmpFile);
        }
    }

    #[Test]
    public function publishDateIsNullWhenMissing(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'md_test_');
        file_put_contents($tmpFile, "---\ntitle: No Date\n---\nbody");

        try {
            $this->assertNull($this->loader->load($tmpFile)->getPublishDate());
        } finally {
            unlink($tmpFile);
        }
    }

    #[Test]
    public function toArrayContainsAllPageData(): void
    {
        $page = $this->loader->load($this->fixturesDir . '/index.md');
        $data = $page->toArray();

        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('template', $data);
        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('publish_date', $data);
        $this->assertArrayHasKey('synopsis', $data);
        $this->assertArrayHasKey('image', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('tags', $data);
        $this->assertArrayHasKey('body', $data);
        $this->assertArrayHasKey('metadata', $data);
    }

    #[Test]
    public function metadataContainsOnlyUnknownFields(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'md_test_');
        file_put_contents($tmpFile, "---\ntitle: Test\nslug: test\ncustom_field: value\n---\nbody");

        try {
            $page = $this->loader->load($tmpFile);
            $this->assertArrayNotHasKey('title', $page->getMetadata());
            $this->assertArrayNotHasKey('slug', $page->getMetadata());
            $this->assertSame('value', $page->getMetadata()['custom_field']);
        } finally {
            unlink($tmpFile);
        }
    }
}
