---
title: Templating with Twig
template: default
publish_date: 20.02.2026
synopsis: How FolioPHP uses Twig templates to render Markdown content into HTML pages.
image: post-twig.svg
categories:
  - Tutorial
tags:
  - Twig
  - Templates
  - HTML
---

# Templating with Twig

FolioPHP uses [Twig](https://twig.symfony.com/) as its template engine. Twig is safe, fast, and expressive — it keeps logic out of your HTML.

## Template Inheritance

Templates use inheritance to avoid repetition. The `layout.html.twig` defines the outer shell — `<html>`, `<head>`, `<header>`, `<footer>` — while child templates fill in the `{% block content %}` section.

```twig
{# layout.html.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}{% endblock %}</title>
</head>
<body>
    <main>{% block content %}{% endblock %}</main>
</body>
</html>
```

```twig
{# default.html.twig #}
{% extends 'layout.html.twig' %}

{% block title %}{{ content.title }}{% endblock %}

{% block content %}
    <h1>{{ content.title }}</h1>
    {{ content.body|raw }}
{% endblock %}
```

## Available Variables

Inside any template, the `content` variable contains everything from the page's front matter plus the rendered body:

| Variable | Type | Example |
|---|---|---|
| `content.title` | string | `"Hello World"` |
| `content.slug` | string | `"hello-world"` |
| `content.publish_date` | DateTimeImmutable | — |
| `content.synopsis` | string | `"Short desc…"` |
| `content.image` | string | `"hero.jpg"` |
| `content.categories` | array | `["Tutorial"]` |
| `content.tags` | array | `["PHP", "Twig"]` |
| `content.body` | string (HTML) | `"<p>…</p>"` |
| `content.metadata` | array | any extra fields |

## Formatting Dates

The `publish_date` is a `DateTimeImmutable` object, so Twig's `date` filter works directly:

```twig
<time datetime="{{ content.publish_date|date('Y-m-d') }}">
    {{ content.publish_date|date('d F Y') }}
</time>
```

## Choosing a Template

The front matter `template` key selects which template file to use (defaults to `default`):

```yaml
---
title: My Page
template: landing
---
```

This renders `templates/landing.html.twig`.

## Custom Metadata

Any front matter field not recognized by FolioPHP lands in `content.metadata`:

```yaml
---
title: My Post
author: Jane Doe
reading_time: 5
---
```

```twig
<p>By {{ content.metadata.author }} · {{ content.metadata.reading_time }} min read</p>
```

## Further Reading

- [Official Twig Documentation](https://twig.symfony.com/doc/)
- [Back to Hello World](/blog/hello-world)
- [Home](/)
