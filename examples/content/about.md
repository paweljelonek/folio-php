---
title: About
template: default
publish_date: 01.01.2026
synopsis: Learn what FolioPHP is, why it was built, and who it is for.
image: about.svg
categories:
  - Meta
tags:
  - About
  - Philosophy
---

# About FolioPHP

FolioPHP was built for developers who want a simple, fast website without the overhead of a full CMS.

## Philosophy

> Complexity is the enemy of reliability.

Most websites don't need a database. A personal blog, a portfolio, a product landing page — these are just text, images, and links. FolioPHP embraces that simplicity.

## Who Is It For?

- **Developers** who want to own their content in plain text files
- **Writers** who prefer Markdown over WYSIWYG editors
- **Teams** who want content under version control alongside the code

## Technology Stack

| Layer       | Technology         |
|-------------|--------------------|
| Routing     | Slim Framework 4   |
| Templates   | Twig 3             |
| Content     | Markdown + YAML    |
| Cache       | Filesystem         |
| Container   | PHP-DI 7           |

## Content Format

Every page is a Markdown file with optional YAML front matter:

```markdown
---
title: My Page
publish_date: 28.04.2026
synopsis: A short description for SEO.
image: my-image.jpg
categories:
  - Tech
tags:
  - PHP
---

Your content here.
```

## Contributing

FolioPHP is open source. Pull requests and issues are welcome.

- [GitHub Repository](#)
- [Report a Bug](#)
- [Request a Feature](#)
