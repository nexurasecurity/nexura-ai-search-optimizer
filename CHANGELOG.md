# Changelog

All notable changes to **Nexura AI Search Optimizer** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-11-15

### Added
- `llmstxt.org` v2 HTTP `Link:` discovery headers (`rel="describedby"; type="text/plain"` and `rel="alternate"; type="text/markdown"`) and `.md` URL extension mirror (`/post-slug.md`).
- IndexNow Instant Indexing Protocol (`api.indexnow.org`) with automatic virtual key verification and instant pings for Bing, Microsoft Copilot, and ChatGPT Search.
- `[nexura_ai_summary]` shortcode and Post Editor Meta Box controls for top-of-post AI Key Takeaways (TL;DR) boxes, integrated into `/llms.txt`, `/llms-full.txt`, and `SpeakableSpecification` schema.
- Automatic `FAQPage`, `SpeakableSpecification`, Author E-E-A-T (`description`, `url`, `sameAs`), and external `citation` JSON-LD Schema extraction.
- 2026 Live User-Triggered AI Browsing Agent controls (`ChatGPT-User`, `Claude-User`, `Perplexity-User`) and extended search/training bot directives (`DuckAssistBot`, `Amazonbot`, `YouBot`, `Meta-ExternalAgent`).
- Human AI Referral Traffic Tracker in Pro Bot Analytics to measure real visitor clicks from ChatGPT, Perplexity, Claude, Gemini, Microsoft Copilot, and You.com.
- 2026 AI Models in Pro AI Agent (`gemini-2.5-flash`, `gemini-2.5-pro`, `gemini-2.0-flash`, `gpt-4.1`, `gpt-4.1-mini`, `claude-sonnet-4-20250514`, `claude-3-7-sonnet-latest`) with automatic migration for retired Gemini 1.5 models.
- AI Crawler, WAF & Physical `robots.txt` Health Check diagnostics card on the Dashboard.

### Changed
- Improved live synchronization of Gutenberg Block Editor and Classic TinyMCE Editor when applying 1-Click AI Rewrites from the Post Edit screen.
- Improved request-level static caching for excluded utility page lookups, eliminating N+1 database queries on admin list tables.

### Fixed
- Virtual `robots.txt` syntax compliance (`User-agent: *`), Bot Analytics hook priority on `/llms.txt` and `.md` endpoints, WooCommerce plugin load-order hook registration, and Editor/Author role capabilities for on-page GEO scans.

---

## [1.0.0] - 2026-11-15

### Added
- **Initial Public Release of Nexura AI Search Optimizer**.
- **Community-Standard `/llms.txt` & `/llms-full.txt` Generator**:
  - Dynamically generated using WordPress rewrite rules with zero physical file clutter.
  - Automatically aggregates key pages, posts, and documentation with token-efficient descriptions.
- **Clean Markdown Output Engine (`?format=markdown`)**:
  - Strips HTML navigation menus, sidebars, footers, scripts, and advertisements.
  - Returns lightweight, token-optimized Markdown that AI engines and search crawlers prefer.
- **Evidence-Based GEO Citation Audit Engine**:
  - Princeton research-backed audit factors (BLUF, statistical fact density, heading structure, E-E-A-T, citation authority).
  - On-page score (0–100) with actionable recommendations to maximize AI search citations.
- **Smart AI Bot & Crawler Permissions**:
  - Distinct management for AI search bots (`OAI-SearchBot`, `Claude-SearchBot`, `PerplexityBot`) vs model training scrapers (`GPTBot`, `ClaudeBot`, `Google-Extended`).
  - Native virtual `robots.txt` rules generation.
- **SEO Plugin Compatibility Mode**:
  - 100% zero-conflict integration with Yoast SEO, Rank Math, All in One SEO, SEOPress, and Schema Pro.
- **Single Post/Page Exclusions**:
  - Granular control to exclude sensitive or private pages from `/llms.txt` and AI search indexing.
- **Native WordPress Admin UI/UX**:
  - Clean, accessible admin design supporting all standard WordPress color palettes and Dark Mode.
