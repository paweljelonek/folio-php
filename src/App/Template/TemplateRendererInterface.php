<?php

declare(strict_types=1);

namespace App\Template;

use App\Content\Page;

interface TemplateRendererInterface
{
    public function render(Page $page): string;
}
