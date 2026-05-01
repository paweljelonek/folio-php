---
title: Hello, World — First Post
template: default
publish_date: 10.01.2026
synopsis: The very first post on this example site. A walkthrough of FolioPHP basics.
image: post-hello.svg
categories:
  - Tutorial
tags:
  - Getting Started
  - Markdown
  - Routing
---

# Hello, World — First Post

Welcome to the example blog. This post walks through the basics of FolioPHP so you can get a feel for how everything fits together.

## Creating Content

Every page on your site corresponds to a Markdown file in the `content/` directory. The URL maps directly to the file path:

| File | URL |
|---|---|
| `content/index.md` | `/` |
| `content/about.md` | `/about` |
| `content/blog/hello-world.md` | `/blog/hello-world` |

## Front Matter

At the top of each file, you can add YAML front matter between `---` delimiters:

```yaml
---
title: My Post
slug: my-post
publish_date: 10.01.2026
synopsis: A short summary shown in search results.
image: my-image.jpg
categories:
  - Tutorial
tags:
  - PHP
  - Markdown
---
```

These fields are available in your Twig templates as `content.title`, `content.publish_date`, `content.tags`, etc.

## Markdown Syntax

You can use the full CommonMark spec plus GitHub extensions:

**Bold text**, *italic text*, ~~strikethrough~~.

> Blockquotes look like this. They're great for pulling out key quotes.

Inline `code` and fenced code blocks:

```php
<?php

echo "Hello, FolioPHP!";
```

### Lists

Unordered:

- First item
- Second item
- Third item with a [link](#)

Ordered:

1. Install dependencies with `composer install`
2. Copy `.env.example` to `.env`
3. Run `php -S localhost:8080 -t public/`

## Images

Images placed in `examples/assets/images/` are available at `/assets/images/`:

![Placeholder image](/assets/images/post-hello.svg)

## What's Next?

- Explore the [About](/about) page
- Go back [Home](/)
- Read the second post: [Templating with Twig](/blog/twig-templates)
