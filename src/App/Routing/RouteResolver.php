<?php

declare(strict_types=1);

namespace App\Routing;

final class RouteResolver
{
    private const ALLOWED_EXTENSIONS = ['md', 'markdown'];
    private const ERROR_DIR = '_errors';

    public function __construct(private readonly string $contentPath) {}

    public function resolve(string $urlPath): ?string
    {
        $urlPath  = trim($urlPath, '/');
        $segments = $urlPath === '' ? ['index'] : explode('/', $urlPath);

        if ($this->hasInvalidSegments($segments)) {
            return null;
        }

        foreach (self::ALLOWED_EXTENSIONS as $ext) {
            $candidate = sprintf('%s/%s.%s', $this->contentPath, implode('/', $segments), $ext);
            $real      = realpath($candidate);

            if ($real !== false && is_file($real) && $this->isConfined($real)) {
                return $real;
            }
        }

        return null;
    }

    public function resolveError(int $code): ?string
    {
        foreach (self::ALLOWED_EXTENSIONS as $ext) {
            $candidate = sprintf('%s/%s/%d.%s', $this->contentPath, self::ERROR_DIR, $code, $ext);
            $real      = realpath($candidate);

            if ($real !== false && is_file($real) && $this->isConfined($real)) {
                return $real;
            }
        }

        return null;
    }

    /** @param array<string> $segments */
    private function hasInvalidSegments(array $segments): bool
    {
        foreach ($segments as $segment) {
            if ($segment === ''
                || $segment === '.'
                || $segment === '..'
                || str_starts_with($segment, '_')
                || str_contains($segment, "\0")
            ) {
                return true;
            }
        }

        return false;
    }

    private function isConfined(string $realPath): bool
    {
        $root = realpath($this->contentPath);

        return $root !== false
            && str_starts_with($realPath, $root . DIRECTORY_SEPARATOR);
    }
}
