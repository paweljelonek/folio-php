# FolioPHP

Lightweight flat-file PHP framework. Write content in Markdown with YAML front matter, render it through Twig templates. URL routing mirrors the file structure. Built-in file cache, console commands and full Docker support.

## Background

I started working on this framework over two years ago because I needed a simple tool to build my own website. Popular CMS platforms like WordPress were too large and complex for what I actually required. My focus was on creating content using simple Markdown files and using Git for easy versioning.

The solutions available at the time did not meet my expectations. I wanted something that was easy to implement, fast, lightweight, and most importantly, did not require a database. Now, after more than two years, I have finally finished the project. I plan to continue developing this tool in my free time and soon use it for other projects I have been planning to do.

## Requirements

- PHP 8.3+
- Composer 2
- Or: Docker + Docker Compose

## Installation

### With Docker (recommended)

```bash
git clone https://github.com/paweljelonek/folio-php.git
cd folio-php
cp .env.example .env
make build
make up          # app + nginx (port 8080)
make install     # installs composer dependencies inside container
```

Open [http://localhost:8080](http://localhost:8080).

### Without Docker

```bash
git clone https://github.com/paweljelonek/folio-php.git
cd folio-php
composer install
cp .env.example .env
```

Point your web server document root to `public/`. Example with PHP built-in server:

```bash
php -S localhost:8080 -t public
```

## Configuration

All settings live in `.env`:

| Variable | Default | Description |
|---|---|---|
| `APP_DEBUG` | `true` | Show error details |
| `SHOW_RENDER_TIME` | `false` | Display page render time bar at bottom of page |
| `CONTENT_PATH` | `content` | Directory with `.md` files |
| `TEMPLATES_PATH` | `resources/templates` | Directory with Twig templates |
| `CACHE_DRIVER` | `null` | `null` — disabled, `file` — filesystem cache |
| `CACHE_DIR` | `var/cache` | Directory where cache files are stored |
| `CACHE_TTL` | `3600` | Cache lifetime in seconds (`0` = never expires) |
| `NGINX_PORT` | `8080` | Host port for Nginx (Docker only) |

## Content

Pages are Markdown files in `content/`. URL routing mirrors the directory structure:

| URL | File |
|---|---|
| `/` | `content/index.md` |
| `/about` | `content/about.md` |
| `/blog/post` | `content/blog/post.md` |

### Front matter

Every `.md` file can include a YAML front matter block. All fields are optional except `title`:

```yaml
---
title: My Page
template: default
publish_date: 28.04.2026
synopsis: Short description shown in meta tags.
image: hero.jpg
categories:
  - Tutorial
tags:
  - PHP
  - Markdown
---

# My Page

Page content in **Markdown**.
```

| Field | Default | Description |
|---|---|---|
| `title` | `""` | Page title |
| `template` | `default` | Twig template name (without `.html.twig`) |
| `publish_date` | `null` | Publication date in `dd.mm.yyyy` format |
| `synopsis` | `""` | Short description (useful for meta tags) |
| `image` | `""` | Hero image filename from assets |
| `categories` | `[]` | List of categories |
| `tags` | `[]` | List of tags |

Any other key lands in `content.metadata` and is accessible in templates.

### 404 page

Create `content/_errors/404.md` to customise the not-found page. Use `template: error` in its front matter.

> Directories and files starting with `_` are never accessible via URL.

## Templates

Templates live in `resources/templates/` and are standard Twig files. The page data is passed as the `content` variable:

```twig
{% extends "layout.html.twig" %}

{% block title %}{{ content.title }}{% endblock %}

{% block content %}
    <h1>{{ content.title }}</h1>

    {% if content.publish_date %}
    <time datetime="{{ content.publish_date|date('Y-m-d') }}">
        {{ content.publish_date|date('d F Y') }}
    </time>
    {% endif %}

    {% for category in content.categories %}
        <span>{{ category }}</span>
    {% endfor %}

    <div>{{ content.body|raw }}</div>
{% endblock %}
```

Available `content` fields:

| Field | Type | Description |
|---|---|---|
| `content.title` | string | Page title |
| `content.template` | string | Template name |
| `content.publish_date` | DateTimeImmutable\|null | Publication date |
| `content.synopsis` | string | Short description |
| `content.image` | string | Hero image filename |
| `content.categories` | array | List of categories |
| `content.tags` | array | List of tags |
| `content.body` | string | Rendered HTML body (use `\|raw`) |
| `content.metadata` | array | Any unrecognised front matter fields |

## Assets

Place CSS, JS and images in a directory of your choice, then link it into `public/` with a symlink:

```bash
php bin/console assets:link
# default: examples/assets → public/assets

php bin/console assets:link --source=my-theme/assets --target=public/assets
php bin/console assets:link --force   # recreate existing symlink
```

## Cache

When `CACHE_DRIVER=file`, each rendered page is saved as a ready-to-serve `.html` file in `CACHE_DIR`. On subsequent requests the HTML file is served directly — no Markdown parsing or template rendering needed.

Cache refresh is controlled by `CACHE_TTL` (seconds). Set to `0` to cache forever and clear manually:

```bash
php bin/console cache:clear
```

## Console

```bash
# Create a symlink from an assets directory into public/
php bin/console assets:link
php bin/console assets:link --source=my-theme/assets --target=public/assets

# Clear all cached pages
php bin/console cache:clear

# List all commands
php bin/console list
```

With Docker:
```bash
make assets-link
make cache-clear
```

## Docker commands (Makefile)

| Command | Description |
|---|---|
| `make up` | Start app + nginx |
| `make down` | Stop all containers |
| `make build` | Rebuild Docker images |
| `make install` | Install Composer dependencies |
| `make test` | Run PHPUnit test suite |
| `make cache-clear` | Clear page cache |
| `make assets-link` | Create assets symlink inside container |
| `make example` | Copy example config and start built-in server |
| `make shell` | Open shell in app container |
| `make logs` | Follow container logs |

## Running tests

```bash
# Local
php vendor/bin/phpunit --testdox

# Docker
make test
```

## Example site

The `examples/` directory contains a fully working demo site that shows FolioPHP in action:

| What | Where |
|---|---|
| Sample pages (home, about, 2 blog posts, 404) | `examples/content/` |
| Layout + page templates with Twig inheritance | `examples/templates/` |
| CSS, JS and SVG placeholder images | `examples/assets/` |
| Ready-to-use `.env` pointing at the example dirs | `examples/.env.example` |

Quick start (no Docker):

```bash
cp examples/.env.example .env
php bin/console assets:link   # public/assets → ../examples/assets
php -S localhost:8080 -t public/
```

Full instructions, URL map and tips for adding your own content:
**[examples/README.md](examples/README.md)**

## Project structure

```
folio-php/
├── bin/
│   └── console                 # CLI entry point
├── config/
│   └── container.php           # DI container definitions
├── content/                    # Your Markdown pages (create this, configure via CONTENT_PATH)
│   ├── _errors/
│   │   └── 404.md
│   └── index.md
├── resources/
│   └── templates/              # Your Twig templates (create this, configure via TEMPLATES_PATH)
├── .docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── php.ini
├── examples/                   # Ready-to-run example site
│   ├── assets/                 # CSS, JS, images
│   ├── content/                # Sample Markdown pages
│   ├── templates/              # Sample Twig templates
│   ├── .env.example            # Example config pointing at examples/
│   └── README.md
├── public/
│   ├── index.php               # Web entry point
│   └── assets -> ../examples/assets   # Symlink created by assets:link
├── src/App/
│   ├── Cache/                  # CacheInterface, FilesystemCache, NullCache
│   ├── Console/                # Console commands
│   ├── Content/                # Markdown loader, front matter parser
│   ├── Http/                   # Page handler, error handler, middleware
│   ├── Routing/                # URL → file resolver
│   └── Template/               # Twig renderer
├── tests/
├── var/cache/                  # File cache
├── .env.example
├── docker-compose.yml
└── Makefile
```

## License

MIT — see [LICENSE](LICENSE).
