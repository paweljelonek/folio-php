<?php

declare(strict_types=1);

namespace App\Tests\Unit\Content;

use App\Content\FrontMatterParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FrontMatterParserTest extends TestCase
{
    private FrontMatterParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FrontMatterParser();
    }

    #[Test]
    public function parsesYamlFrontMatterAndBody(): void
    {
        $raw = "---\ntitle: Hello\ntemplate: default\n---\n## Content";

        $result = $this->parser->parse($raw);

        $this->assertSame('Hello', $result->metadata['title']);
        $this->assertSame('default', $result->metadata['template']);
        $this->assertSame('## Content', $result->body);
    }

    #[Test]
    public function returnsEmptyMetadataWhenNoFrontMatter(): void
    {
        $raw = '## Just content, no front matter';

        $result = $this->parser->parse($raw);

        $this->assertSame([], $result->metadata);
        $this->assertSame($raw, $result->body);
    }

    #[Test]
    public function returnsEmptyMetadataWhenClosingDelimiterMissing(): void
    {
        $raw = "---\ntitle: Hello\n## No closing delimiter";

        $result = $this->parser->parse($raw);

        $this->assertSame([], $result->metadata);
    }

    #[Test]
    public function trimsLeadingWhitespaceBeforeParsing(): void
    {
        $raw = "\n\n---\ntitle: Spaced\n---\nbody";

        $result = $this->parser->parse($raw);

        $this->assertSame('Spaced', $result->metadata['title']);
    }

    #[Test]
    public function parsesMultilineYamlValues(): void
    {
        $raw = "---\ntitle: Test\ncategories:\n  - PHP\n  - Testing\n---\nbody";

        $result = $this->parser->parse($raw);

        $this->assertSame(['PHP', 'Testing'], $result->metadata['categories']);
    }

    #[Test]
    #[DataProvider('emptyDocumentProvider')]
    public function handlesEmptyOrBlankContent(string $raw): void
    {
        $result = $this->parser->parse($raw);

        $this->assertIsArray($result->metadata);
        $this->assertIsString($result->body);
    }

    public static function emptyDocumentProvider(): array
    {
        return [
            'empty string'      => [''],
            'only whitespace'   => ['   '],
            'empty front matter'=> ["---\n---\nbody"],
        ];
    }
}
