# FolioPHP — Example Site

A working example site included with FolioPHP. It demonstrates content structure,
template inheritance, front matter fields, and asset organisation.

## Directory Structure

```
examples/
├── assets/
│   ├── css/main.css       # All styles
│   ├── js/main.js         # Navigation toggle
│   └── images/            # SVG placeholders (replace with real images)
├── content/
│   ├── _errors/404.md     # Custom 404 page
│   ├── index.md           # Home page  →  /
│   ├── about.md           # About page →  /about
│   └── blog/
│       ├── hello-world.md      →  /blog/hello-world
│       └── twig-templates.md   →  /blog/twig-templates
├── templates/
│   ├── layout.html.twig   # Base layout (header, nav, footer)
│   ├── default.html.twig  # Default page template
│   └── error.html.twig    # Error page template
└── .env                   # Ready-to-use config for this example
```

## Quick Start

### 1. Link assets

Assets must be accessible from `public/`. Instead of copying files, create a symlink:

```bash
php bin/console assets:link
```

This creates `public/assets → ../examples/assets`.

To link a custom assets directory:

```bash
php bin/console assets:link --source=my-theme/assets --target=public/assets
```

### 2. Activate the example config

Copy the example `.env` over the project root:

```bash
cp examples/.env .env
```

Or manually set these two lines in your `.env`:

```dotenv
CONTENT_PATH=examples/content
TEMPLATES_PATH=examples/templates
```

### 3. Run the development server

**PHP built-in server (no Docker required):**

```bash
php -S localhost:8080 -t public/
```

Then open [http://localhost:8080](http://localhost:8080).

**Docker:**

```bash
make up
```

Then open [http://localhost:8080](http://localhost:8080).

## Pages

| URL | File |
|---|---|
| `/` | `content/index.md` |
| `/about` | `content/about.md` |
| `/blog/hello-world` | `content/blog/hello-world.md` |
| `/blog/twig-templates` | `content/blog/twig-templates.md` |

## Adding Your Own Content

1. Create a `.md` file anywhere under `examples/content/`
2. Add YAML front matter at the top:

```markdown
---
title: My New Page
slug: my-new-page
publish_date: 29.04.2026
synopsis: A short description.
image: my-image.jpg
categories:
  - General
tags:
  - Example
---

# My New Page

Content goes here.
```

3. Visit the URL matching the file path — no restart needed.

## Resetting

To go back to your own content/templates, restore your `.env`:

```bash
CONTENT_PATH=content
TEMPLATES_PATH=resources/templates
```
