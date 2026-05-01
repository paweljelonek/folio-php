<?php

declare(strict_types=1);

namespace App\Template;

use App\Content\Page;
use Twig\Environment;
use Twig\Error\LoaderError;

final class TwigRenderer implements TemplateRendererInterface
{
    public function __construct(private readonly Environment $twig) {}

    public function render(Page $page): string
    {
        $template = $page->getTemplate() . '.html.twig';

        try {
            return $this->twig->render($template, ['content' => $page->toArray()]);
        } catch (LoaderError $e) {
            throw new TemplateNotFoundException(
                sprintf('Template "%s" not found.', $template),
                previous: $e,
            );
        }
    }
}
