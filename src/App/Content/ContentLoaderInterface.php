<?php

declare(strict_types=1);

namespace App\Content;

interface ContentLoaderInterface
{
    /**
     * @throws ContentNotFoundException when the file does not exist
     */
    public function load(string $filePath): Page;
}
