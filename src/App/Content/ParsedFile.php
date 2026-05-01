<?php

declare(strict_types=1);

namespace App\Content;

final class ParsedFile
{
    public function __construct(
        public readonly array $metadata,
        public readonly string $body,
    ) {}
}
