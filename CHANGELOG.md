# Changelog

All notable changes to **Nexura AI Search Optimizer** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
