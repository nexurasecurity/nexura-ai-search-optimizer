# Contributing to Nexura AI Search Optimizer

First off, thank you for considering contributing to Nexura AI Search Optimizer! It's contributors like you that make this plugin an essential toolkit for Generative Engine Optimization (GEO) in the WordPress community.

## How Can I Contribute?

### Reporting Bugs

Before submitting a bug report, please check existing issues to ensure it has not already been reported. When submitting a bug report, please include:
* A clear, concise title.
* Step-by-step instructions to reproduce the problem.
* The WordPress, PHP, and web server versions.
* Any active SEO plugins (Yoast, Rank Math, AIOSEO, etc.).
* Sample output from `/llms.txt` or `?format=markdown` if applicable.

### Suggesting Enhancements

Feature suggestions and enhancements are welcome! Please include:
* A detailed description of the proposed enhancement.
* The problem it solves or the GEO / AI search advantage it provides.
* Relevant LLM specifications (e.g. `/llms.txt` community standard updates).

### Pull Requests

1. Fork the repository and create your branch from `main`.
2. Follow [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/).
3. Ensure no performance regressions or extraneous external API calls in free core modules.
4. Verify compatibility with major SEO plugins.
5. Submit your pull request!

## Code Guidelines

* **Zero External Dependencies**: Free core features must run locally on native WordPress APIs.
* **Token Efficiency**: Clean Markdown output must strictly minimize token consumption for LLMs.
* **Security First**: All endpoints and settings must use nonces, capability checks (`manage_options`), and sanitized inputs.

Thank you for helping shape the future of AI search on WordPress!

