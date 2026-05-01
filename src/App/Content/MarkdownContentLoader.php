<?php

declare(strict_types=1);

namespace App\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

final class MarkdownContentLoader implements ContentLoaderInterface
{
    private readonly MarkdownConverter $converter;

    public function __construct(private readonly FrontMatterParser $parser)
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function load(string $filePath): Page
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw ContentNotFoundException::forPath($filePath);
        }

        $raw    = file_get_contents($filePath);
        $parsed = $this->parser->parse($raw);
        $body   = (string) $this->converter->convert($parsed->body);

        return Page::fromParsed($parsed->metadata, $body);
    }
}
