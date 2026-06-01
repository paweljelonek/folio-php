<?php

declare(strict_types=1);

namespace App\Content;

use DateTimeImmutable;

final class Page
{
    private const KNOWN_KEYS = ['title', 'template', 'slug', 'publish_date', 'synopsis', 'image', 'categories', 'tags'];

    /**
     * @param array<mixed>         $categories
     * @param array<mixed>         $tags
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private readonly string $title,
        private readonly string $template,
        private readonly string $slug,
        private readonly ?DateTimeImmutable $publishDate,
        private readonly string $synopsis,
        private readonly string $image,
        private readonly array $categories,
        private readonly array $tags,
        private readonly string $body,
        private readonly array $metadata,
    ) {}

    /** @param array<string, mixed> $metadata */
    public static function fromParsed(array $metadata, string $body): self
    {
        $publishDate = null;
        if (isset($metadata['publish_date'])) {
            $parsed = DateTimeImmutable::createFromFormat('d.m.Y', (string) $metadata['publish_date']);
            $publishDate = $parsed !== false ? $parsed : null;
        }

        return new self(
            title:       (string) ($metadata['title'] ?? ''),
            template:    (string) ($metadata['template'] ?? 'default'),
            slug:        (string) ($metadata['slug'] ?? ''),
            publishDate: $publishDate,
            synopsis:    (string) ($metadata['synopsis'] ?? ''),
            image:       (string) ($metadata['image'] ?? ''),
            categories:  (array)  ($metadata['categories'] ?? []),
            tags:        (array)  ($metadata['tags'] ?? []),
            body:        $body,
            metadata:    array_diff_key($metadata, array_flip(self::KNOWN_KEYS)),
        );
    }

    public function getTitle(): string { return $this->title; }
    public function getTemplate(): string { return $this->template; }
    public function getSlug(): string { return $this->slug; }
    public function getPublishDate(): ?DateTimeImmutable { return $this->publishDate; }
    public function getSynopsis(): string { return $this->synopsis; }
    public function getImage(): string { return $this->image; }
    /** @return array<mixed> */
    public function getCategories(): array { return $this->categories; }
    /** @return array<mixed> */
    public function getTags(): array { return $this->tags; }
    public function getBody(): string { return $this->body; }
    /** @return array<string, mixed> */
    public function getMetadata(): array { return $this->metadata; }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'title'        => $this->title,
            'template'     => $this->template,
            'slug'         => $this->slug,
            'publish_date' => $this->publishDate,
            'synopsis'     => $this->synopsis,
            'image'        => $this->image,
            'categories'   => $this->categories,
            'tags'         => $this->tags,
            'body'         => $this->body,
            'metadata'     => $this->metadata,
        ];
    }
}
