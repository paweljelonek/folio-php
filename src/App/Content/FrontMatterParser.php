<?php

declare(strict_types=1);

namespace App\Content;

use Symfony\Component\Yaml\Yaml;

final class FrontMatterParser
{
    private const DELIMITER = '---';

    public function parse(string $rawContent): ParsedFile
    {
        $rawContent = ltrim($rawContent);

        if (!str_starts_with($rawContent, self::DELIMITER . "\n")) {
            return new ParsedFile([], $rawContent);
        }

        $end = strpos($rawContent, "\n" . self::DELIMITER, strlen(self::DELIMITER));

        if ($end === false) {
            return new ParsedFile([], $rawContent);
        }

        $yamlBlock = substr($rawContent, strlen(self::DELIMITER) + 1, $end - strlen(self::DELIMITER) - 1);
        $body      = ltrim(substr($rawContent, $end + strlen(self::DELIMITER) + 1));

        $metadata = Yaml::parse($yamlBlock) ?? [];

        return new ParsedFile(is_array($metadata) ? $metadata : [], $body);
    }
}
