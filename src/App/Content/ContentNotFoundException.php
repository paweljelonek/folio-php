<?php

declare(strict_types=1);

namespace App\Content;

final class ContentNotFoundException extends \RuntimeException
{
    public static function forPath(string $path): self
    {
        return new self(sprintf('Content file not found: %s', $path));
    }
}
