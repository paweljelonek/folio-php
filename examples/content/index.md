---
title: Welcome to FolioPHP
template: default
publish_date: 28.04.2026
synopsis: A lightweight flat-file PHP framework for building elegant websites from Markdown files.
image: hero.svg
categories:
  - General
tags:
  - Getting Started
  - PHP
  - Framework
---

# Welcome to FolioPHP

**FolioPHP** is a lightweight flat-file PHP framework. Write content in Markdown, render it through Twig templates, and ship.

## How It Works

No database. No admin panel. No magic. Just files.

1. Drop a Markdown file into `content/`
2. Add YAML front matter for metadata
3. The URL mirrors the file path

## Features

- **Flat-file** — content lives in `.md` files, version-controlled alongside your code
- **Twig templates** — powerful, safe, designer-friendly templating
- **File cache** — optional caching layer for fast response times
- **Docker ready** — production-grade setup included
- **Zero config** — sensible defaults, override via `.env`

## Quick Example

Create `content/hello.md`:

```yaml
---
title: Hello World
publish_date: 28.04.2026
---

# Hello, World!

This page is now live at `/hello`.
```

That's it. Visit `/hello` and your page is there.

## Next Steps

- Read the [About](/about) page to learn more
- Check out a [sample blog post](/blog/hello-world)
- Browse the [source on GitHub](#)
