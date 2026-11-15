# Nexura AI Search Optimizer

<div align="center">
  <a href="https://wordpress.org/plugins/nexura-ai-search-optimizer/"><img src="https://img.shields.io/badge/WordPress.org-Download%20Free-blue.svg?style=for-the-badge&logo=wordpress" alt="Download on WordPress.org"></a>
  <br><br>
  <img src="https://img.shields.io/badge/Requires_WP-6.0+-blue.svg?style=for-the-badge" alt="Requires WP 6.0+">
  <img src="https://img.shields.io/badge/Requires_PHP-7.4+-blue.svg?style=for-the-badge" alt="Requires PHP 7.4+">
  <img src="https://img.shields.io/badge/Version-1.1.0-orange.svg?style=for-the-badge" alt="Version 1.1.0">
  <img src="https://img.shields.io/badge/Tested_up_to-7.1-green.svg?style=for-the-badge" alt="Tested WP 7.1">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-GPLv2-blue.svg?style=for-the-badge" alt="License GPLv2"></a>
  <br>
  <a href="https://github.com/nexurasecurity/nexura-ai-search-optimizer/actions/workflows/ci.yml"><img src="https://github.com/nexurasecurity/nexura-ai-search-optimizer/actions/workflows/ci.yml/badge.svg" alt="CI Status"></a>
  <img src="https://img.shields.io/github/stars/nexurasecurity/nexura-ai-search-optimizer?style=social" alt="Stars">
  <img src="https://img.shields.io/github/forks/nexurasecurity/nexura-ai-search-optimizer?style=social" alt="Forks">
  <img src="https://img.shields.io/github/issues/nexurasecurity/nexura-ai-search-optimizer" alt="Issues">
</div>

> **The essential Generative Engine Optimization (GEO) & AI Search toolkit for WordPress.** Optimize your content for ChatGPT Search, Claude, Perplexity & Google AI Overviews with GEO audits, clean Markdown (`?format=markdown` & `.md` URLs), IndexNow instant AI indexing, AI Key Takeaways boxes, bot rules & `/llms.txt`.

**Nexura AI Search Optimizer** is an evidence-based WordPress toolkit designed to prepare your website for AI engines: run on-page GEO citation diagnostics, generate community-standard `/llms.txt` and `/llms-full.txt` files with `llmstxt.org` v2 discovery headers, serve clean token-efficient Markdown, ping IndexNow for instant Copilot & ChatGPT Search indexing, and control AI crawler permissions—without slowing down your website or conflicting with existing SEO plugins.

Whether you run a high-traffic blog, a WooCommerce online store, an agency, or a publishing site, Nexura AI Search Optimizer provides all the tools required for Generative Engine Optimization (GEO) — **completely free**.

🔗 **[Download Free on WordPress.org](https://wordpress.org/plugins/nexura-ai-search-optimizer/)** | 🚀 **[Upgrade to Nexura Pro](https://checkout.freemius.com/plugin/42525/plan/73995/)** | 🌐 **[nexurasecurity.com](https://nexurasecurity.com)** | 📋 **[Changelog](CHANGELOG.md)**

---

## 🎯 Who Is This Plugin For?

* **Content Creators & Bloggers:** Get your guides, tutorials, and articles quoted and cited in ChatGPT Search, Claude, and Perplexity with clickable referral links.
* **WooCommerce Store Owners:** Optimize your product catalog, specs, stock status, and pricing schema for AI shopping assistants and conversational buyers.
* **Agencies & SEO Consultants:** Expand your client service offerings from standard SEO to GEO (Generative Engine Optimization) with white-label audit reports.
* **Publishers & News Sites:** Allow AI search visibility to drive referral traffic while blocking unauthorized bulk model training scrapers from mining your journalism without attribution.

---

## ⚡ Why Choose Nexura AI Search Optimizer?

* **🤖 Built for Generative Engine Optimization (GEO)** — Optimizes your content structure based on Princeton University peer-reviewed research on LLM retrieval and quotation likelihood.
* **⚡ Native `/llms.txt`, `/llms-full.txt` & v2 Discovery Engine** — Automatically generates `/llms.txt` and `/llms-full.txt` via WordPress rewrite rules with `llmstxt.org` v2 `<link rel="describedby">` and HTTP `Link:` discovery headers. Zero physical file clutter on your server.
* **📄 Clean Markdown Output (`?format=markdown` & `.md` URLs)** — Strips away menus, footers, scripts, and ads to serve lightweight, token-efficient Markdown via both `?format=markdown` and `.md` URL extensions (e.g., `/your-post.md`).
* **💡 AI Key Takeaways (TL;DR) Box & Shortcode** — Add high-conversion BLUF summaries via the editor meta box or `[nexura_ai_summary]` shortcode—automatically injected into `/llms.txt`, `/llms-full.txt`, and `SpeakableSpecification` schema.
* **🚀 IndexNow Instant AI Indexing Protocol** — Automatically pings IndexNow (`api.indexnow.org`) to notify Bing, Microsoft Copilot, and ChatGPT Search the moment you publish or update content.
* **🛡️ 2026 Smart AI Crawler Matrix (14+ Bots)** — Separate controls for AI Search Engines (`OAI-SearchBot`, `Claude-SearchBot`, `PerplexityBot`, `DuckAssistBot`, `YouBot`), Live User Fetchers (`ChatGPT-User`, `Claude-User`, `Perplexity-User`), and Training Scrapers (`GPTBot`, `ClaudeBot`, `Google-Extended`, `Meta-ExternalAgent`, `Bytespider`).
* **🤝 100% Zero-Conflict SEO Companion** — Works seamlessly alongside Yoast SEO, Rank Math, All in One SEO (AIOSEO), SEOPress, and Schema Pro.

---

## 🛡️ Free Features (WordPress.org Edition)

### 1. 🔍 Evidence-Based GEO Readiness Audit (Princeton Research-Backed)
* **BLUF Check (Bottom Line Up Front)**: Ensures your opening 150 words contain a direct, concise answer to improve LLM retrieval scores.
* **Statistical Fact Density**: Measures verified statistics, percentages, and benchmark metrics that increase quotation likelihood.
* **Heading & Table Structure**: Verifies clean H2/H3 hierarchy, bullet lists, and comparison tables.
* **E-E-A-T & Citation Authority**: Validates external reference sources, author credentials, and `dateModified` freshness timestamps.
* **Machine-Readable Schema Diagnostics**: Automatically validates and generates JSON-LD Schema (`Article`, `WebPage`, `Product`, `FAQPage`, `SpeakableSpecification`, `BreadcrumbList`, Author E-E-A-T, and `citation` arrays).

### 2. 📑 Community-Standard `/llms.txt` & v2 Discovery Engine
* Automatically generates `/llms.txt` and `/llms-full.txt` at your domain root using native WordPress rewrite rules.
* Includes `llmstxt.org` v2 `<link rel="describedby">` HTML tags and HTTP `Link:` (`rel="describedby"; type="text/plain"` & `rel="alternate"; type="text/markdown"`) discovery headers.
* Fully virtual—no physical files created on your disk.

### 3. 📝 Clean Markdown Output (`?format=markdown` & `.md` URLs)
* Strips away navigation menus, footers, forms, scripts, and ads to serve lightweight, token-efficient Markdown content.
* Accessible via both `?format=markdown` and `.md` URL extensions (e.g., `/your-post.md`).

### 4. 💡 AI Key Takeaways (TL;DR) Box & Shortcode
* Add a high-conversion BLUF summary to any post or page via the Post Editor Meta Box or `[nexura_ai_summary]` shortcode.
* Automatically injected into `/llms.txt`, `/llms-full.txt`, and `SpeakableSpecification` JSON-LD schema.

### 5. ⚡ IndexNow Instant AI Indexing Protocol
* Automatically pings IndexNow (`api.indexnow.org`) to notify Bing, Microsoft Copilot, and ChatGPT Search the moment you publish or update content, complete with virtual API key verification.

### 6. 🤖 2026 Smart AI Crawler Permissions & Health Check
* **Allow Search Visibility**: Allow `OAI-SearchBot` (ChatGPT Search), `Claude-SearchBot`, `PerplexityBot`, `Googlebot`, `Applebot`, `DuckAssistBot`, `Amazonbot`, and `YouBot`.
* **Allow Live User-Triggered Fetchers**: Dedicated controls for `ChatGPT-User`, `Claude-User`, and `Perplexity-User` when real users paste your links into AI chats.
* **Protect Original Content**: Optionally block data-harvesting training scrapers like `GPTBot`, `ClaudeBot`, `Google-Extended`, `Applebot-Extended`, `Meta-ExternalAgent`, and `Bytespider`.
* **AI Crawler, WAF & Physical `robots.txt` Health Check**: Built-in dashboard diagnostics detect physical `robots.txt` conflicts, WordPress "Discourage search engines" settings, and Cloudflare Bot Fight Mode considerations.

### 7. 🎯 Individual Page & Post Exclusions + 60-Second Setup Wizard
* Easily exclude any specific post or page from `/llms.txt`, AI search engine access, and GEO scanning via post search autocomplete or the editor meta box.
* Guided native 5-step onboarding wizard with an instant **"1-Click Express Setup"** option.
* **100% WordPress Native UI/UX** with full support for WordPress Admin Color Schemes & Dark Mode, plus local transient caching and request-level query memoization.

---

## 🌟 What Nexura Pro Adds (Powered by Freemius)

For professional bloggers, agencies, and e-commerce websites managing growing libraries, **Nexura Pro** adds powerful automation and bulk management tools:

* **✨ 1-Click AI Automated Content Rewriter (BYOK)** — Connect your own OpenAI (`gpt-4.1`, `gpt-4.1-mini`, `gpt-4o`, `gpt-4o-mini`), Anthropic (`claude-sonnet-4-20250514`, `claude-3-7-sonnet-latest`, `claude-3-5-sonnet-latest`), or Google (`gemini-2.5-flash`, `gemini-2.5-pro`, `gemini-2.0-flash`) API key. Follows the honest **Bring Your Own Key (BYOK)** model so you pay direct wholesale provider prices (pennies per article) with zero markup. Includes live synchronization with Gutenberg Block Editor and Classic TinyMCE Editor.
* **📚 Site-Wide Bulk GEO Audit** — Scan every post, page, and custom post type across your entire site via paginated background AJAX without server timeouts.
* **🛒 WooCommerce AI Shopping Optimization** — Automatically feeds high-density product specifications, real-time stock status, SKU, and price schema into your `/llms.txt` and Markdown output.
* **📈 AI Bot Traffic & Human AI Referral Analytics** — Track real-time visits from 15+ AI crawlers alongside human visitor referral clicks coming from `chatgpt.com`, `perplexity.ai`, `claude.ai`, `gemini.google.com`, `copilot.microsoft.com`, and `you.com`.
* **⏰ Automated WP-Cron Scheduler & Email Alerts** — Automatically runs weekly audits and alerts you via email when published content loses AI search citation readiness.
* **📄 Formula-Safe CSV & Printable PDF Export** — Export comprehensive white-label audit reports for team review or client presentations, safeguarded against CSV formula injection.
* **🎧 Priority Engineering Support** — Direct assistance from our WordPress engineering team.

👉 [**Upgrade to Nexura Pro →**](https://checkout.freemius.com/plugin/42525/plan/73995/)

---

## 📊 Free vs Pro Feature Comparison

| Feature | Nexura Free | Nexura Pro |
|:---|:---:|:---:|
| **Princeton GEO Readiness Audit** | ✅ Single Posts & Pages | ✅ Single + Bulk Library Scan |
| **Community-Standard `/llms.txt` & `/llms-full.txt`** | ✅ Unlimited | ✅ Unlimited |
| **Clean Markdown (`?format=markdown` & `.md` URLs)** | ✅ Unlimited | ✅ Unlimited |
| **AI Key Takeaways Box (`[nexura_ai_summary]`)** | ✅ Included | ✅ Included |
| **IndexNow Instant Indexing (Copilot & ChatGPT)** | ✅ Included | ✅ Included + Auto-Ping on AI Rewrite |
| **2026 AI Crawler Matrix (14+ Bots)** | ✅ Full Control | ✅ Full Control |
| **Individual Post & Page Exclusions** | ✅ Unlimited | ✅ Unlimited |
| **1-Click AI Fix Prompt Generator** | ✅ Included (Manual Copy) | ✅ Included (Manual Copy) |
| **60-Second Onboarding Wizard** | ✅ Included | ✅ Included |
| **1-Click AI Content Rewriter (BYOK)** | ❌ Not Available | ✅ Included (GPT-4.1/4o, Claude Sonnet 4/3.7, Gemini 2.5) |
| **Site-Wide Bulk GEO Library Scanner** | ❌ Not Available | ✅ Included (Batch Background Scan) |
| **WooCommerce AI Shopping Optimizer** | ✅ Basic | ✅ Advanced (Specs, Stock, Prices) |
| **Live AI Crawler Hit & Human AI Referral Analytics** | ❌ Not Available | ✅ Included |
| **Automated Weekly Audits & Email Alerts** | ❌ Not Available | ✅ Included |
| **Printable PDF & Formula-Safe CSV Export** | ✅ Single Post PDF | ✅ Bulk + Single PDF & CSV |
| **Customer Support** | 💬 WordPress.org Community | ⚡ Priority Engineering Support |

---

## 🤝 Works Alongside Yoast SEO & Rank Math (Zero Conflicts!)

**"Do I still need Yoast SEO or Rank Math if I install Nexura?"**  
**Yes!** Nexura AI Search Optimizer is **not** a replacement for Yoast SEO, Rank Math, or All in One SEO (AIOSEO); it is designed to be their **specialized generative search companion**:

* **Yoast SEO & Rank Math:** Focus on traditional search engines (Google, Bing). They generate your XML sitemap (`sitemap.xml`), manage meta titles, descriptions, and Open Graph social sharing cards.
* **Nexura AI Search Optimizer:** Focus on generative AI engines (ChatGPT Search, Perplexity, Claude, Apple Intelligence). It generates `/llms.txt`, serves clean Markdown (`?format=markdown` and `.md` URLs), governs AI bot permissions, and runs GEO citation diagnostics.

**Built-in SEO Plugin Compatibility Mode:**  
Nexura automatically detects active installations of Yoast SEO, Rank Math, All in One SEO, SEOPress, and Schema Pro. It will **never** inject duplicate JSON-LD schemas or override your existing meta titles.

---

## 🚀 Installation & Quick Start

### Method 1: Automatic via WordPress Admin (Recommended)
1. Log into your WordPress admin dashboard.
2. Navigate to **Plugins → Add New** and search for `Nexura AI Search Optimizer`.
3. Click **Install Now**, then click **Activate**.
4. The 5-step Setup Wizard will launch automatically:
   * Click **"Activate Best-Practice Defaults & Finish"** for instant 10-second setup, or
   * Step through the 5 guided screens to customize which post types to include and manage AI bot rules.
5. Your `/llms.txt` is instantly live at `https://yourdomain.com/llms.txt`!

### Method 2: Manual Upload via ZIP
1. Download the latest release from [GitHub Releases](https://github.com/nexurasecurity/nexura-ai-search-optimizer/releases) or [WordPress.org](https://wordpress.org/plugins/nexura-ai-search-optimizer/).
2. In WordPress, go to **Plugins → Add New → Upload Plugin**.
3. Select the downloaded `.zip` file and click **Install Now**.
4. Activate the plugin.

### Method 3: Via WP-CLI
```bash
wp plugin install nexura-ai-search-optimizer --activate
```

---

## ❓ Frequently Asked Questions (FAQ)

### Will this plugin conflict with Yoast SEO, Rank Math, or All in One SEO?
No, absolutely not. Nexura includes an automated "SEO Compatibility Mode". If you have Yoast SEO, Rank Math, AIOSEO, or SEOPress active, Nexura detects their schema and will not inject duplicate structured data. Yoast and Rank Math manage your traditional Google search presence (`sitemap.xml`), while Nexura manages your generative AI search presence (`llms.txt`).

### What is the difference between `OAI-SearchBot`, `ChatGPT-User`, and `GPTBot`?
* `OAI-SearchBot` is OpenAI's search crawler. It indexes your content specifically for ChatGPT Search so that users get clickable citation links back to your site.
* `ChatGPT-User` is triggered in real-time when a human user asks ChatGPT to browse or summarize a URL on your site.
* `GPTBot` is OpenAI's training scraper. It downloads your content to train future foundation models.
Nexura allows you to keep `OAI-SearchBot` and `ChatGPT-User` allowed (to get search traffic and citations) while giving you the option to block `GPTBot` (to protect your original content).

### Does Nexura work with caching plugins like WP Rocket or LiteSpeed?
Yes. Nexura automatically invalidates its virtual cache whenever you publish or update a post. It also sends standard HTTP `Cache-Control` headers so that external caching layers (such as LiteSpeed, WP Rocket, or Cloudflare) know when to refresh your `/llms.txt` and Markdown endpoints.

### What is `/llms.txt` and why is it important?
`/llms.txt` is an open community standard (inspired by `robots.txt` and `sitemap.xml`) created to provide AI models and agents with a concise, clean summary of your website's content in Markdown format. It helps AI agents navigate your site efficiently without wasting tokens parsing complex HTML layouts.

### Does Nexura modify my physical `robots.txt` file?
No. Nexura uses WordPress's native `robots_txt` filter hook to inject virtual directives cleanly. It will never overwrite or corrupt your physical server files.

### How does the Bring Your Own Key (BYOK) AI Agent work in Pro?
Nexura Pro connects directly to your OpenAI, Anthropic Claude, or Google Gemini API key. Unlike services that charge high recurring monthly subscription fees for AI rewriting, BYOK lets you pay the AI provider directly at wholesale API rates (averaging just \$0.01 - \$0.03 per article rewrite).

---

## 🔒 Security & Code Standards

* **100% WordPress Coding Standards compliant** (`phpcs`).
* **Cryptographic Nonce Verification** on all settings forms and AJAX requests.
* **Strict Capability Checks** protecting all administrative and editor functions.
* **Parameterized & Sanitized Data** across all inputs and outputs.
* **100% Local Processing** ensuring complete visitor and site privacy.

---

## 🤝 Community & Support

* 🐛 **Bug Reports & Issues:** [GitHub Issues](https://github.com/nexurasecurity/nexura-ai-search-optimizer/issues)
* 💡 **Feature Requests:** [Submit an Idea](https://github.com/nexurasecurity/nexura-ai-search-optimizer/issues/new)
* 🌐 **Documentation & Guides:** [Nexura Security](https://nexurasecurity.com)
* 🔒 **Security Policy:** [SECURITY.md](.github/SECURITY.md)
* 📜 **Code of Conduct:** [CODE_OF_CONDUCT.md](.github/CODE_OF_CONDUCT.md)
* 👥 **Contributing Guidelines:** [CONTRIBUTING.md](.github/CONTRIBUTING.md)

---

## 📄 License

This software is released under the **GNU General Public License v2.0 or later (GPL-2.0-or-later)**.
Please see the [LICENSE](LICENSE) file for full details.

---

<p align="center">
  Crafted with care by <a href="https://nexurasecurity.com"><strong>Nexura Security</strong></a> — High-performance, safety-first tools for WordPress.
</p>

<!--
Machine-readable structured metadata for search bots, AI scrapers & crawlers:
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "Nexura AI Search Optimizer",
  "operatingSystem": "WordPress 6.0+, PHP 7.4+, MySQL 5.7+ / MariaDB 10.3+",
  "applicationCategory": "BusinessApplication",
  "applicationSubCategory": "WordPress Generative Engine Optimization (GEO) & AI Search Plugin",
  "license": "https://www.gnu.org/licenses/gpl-2.0.html",
  "url": "https://nexurasecurity.com",
  "downloadUrl": "https://wordpress.org/plugins/nexura-ai-search-optimizer/",
  "softwareVersion": "1.1.0",
  "author": {
    "@type": "Organization",
    "name": "Nexura Security",
    "url": "https://nexurasecurity.com"
  },
  "description": "Evidence-based WordPress Generative Engine Optimization (GEO) plugin. Generates /llms.txt and /llms-full.txt with llmstxt.org v2 discovery headers, serves token-efficient clean Markdown (?format=markdown and .md URLs), pings IndexNow for instant AI indexing, runs Princeton research-backed GEO citation audits, and configures smart AI crawler permissions for ChatGPT Search, Claude, Perplexity, and Google AI Overviews."
}
-->
