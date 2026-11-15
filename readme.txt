=== Nexura AI Search Optimizer ===
Contributors: prokashsarker2026, nexurasecurity, freemius
Tags: llms.txt, geo, ai search, generative ai, seo
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Optimize content for ChatGPT Search, Claude, Perplexity & Google AI Overviews with GEO audits, clean Markdown, bot rules & /llms.txt.

== Description ==

Welcome to the next generation of search engine optimization: **Generative Engine Optimization (GEO) & AI Search Readiness**.

In 2026, web search has fundamentally evolved. Millions of users no longer click traditional blue links on search engines; instead, AI search assistants—including ChatGPT Search, Perplexity AI, Claude, and Google AI Overviews—browse the web in real-time, synthesize information, and quote authoritative sources directly with clickable citation links.

**Nexura AI Search Optimizer** is an evidence-based WordPress toolkit designed to prepare your website for AI engines: run on-page GEO citation diagnostics, generate community-standard `/llms.txt` and `/llms-full.txt` files, serve clean token-efficient Markdown, and control AI crawler permissions—without slowing down your website or conflicting with existing SEO plugins.

= Who Is This Plugin For? =

* **Content Creators & Bloggers:** Get your guides, tutorials, and articles quoted and cited in ChatGPT Search, Claude, and Perplexity with clickable referral links.
* **WooCommerce Store Owners:** Optimize your product catalog, specs, stock status, and pricing schema for AI shopping assistants and conversational buyers.
* **Agencies & SEO Consultants:** Expand your client service offerings from standard SEO to GEO (Generative Engine Optimization) with white-label audit reports.
* **Publishers & News Sites:** Allow AI search visibility to drive referral traffic while blocking unauthorized bulk model training scrapers from mining your journalism without attribution.

= Works Alongside Yoast SEO & Rank Math (Zero Conflicts!) =

**"Do I still need Yoast SEO or Rank Math if I install Nexura?"**
**Yes!** Nexura AI Search Optimizer is **not** a replacement for Yoast SEO, Rank Math, or All in One SEO (AIOSEO); it is designed to be their **specialized generative search companion**:

* **Yoast SEO & Rank Math:** Focus on traditional search engines (Google, Bing). They generate your XML sitemap (`sitemap.xml`), manage meta titles, descriptions, and Open Graph social sharing cards.
* **Nexura AI Search Optimizer:** Focuses on generative AI engines (ChatGPT Search, Perplexity, Claude, Apple Intelligence). It generates `/llms.txt`, serves clean Markdown (`?format=markdown` and `.md` URLs), governs AI bot permissions, and runs GEO citation diagnostics.

**Built-in SEO Plugin Compatibility Mode:**
Nexura automatically detects active installations of Yoast SEO, Rank Math, All in One SEO, SEOPress, and Schema Pro. It will **never** inject duplicate JSON-LD schemas or override your existing meta titles. It works harmoniously in the background so you get comprehensive search visibility: Organic Google rankings + AI Search citations!

= Key Free Features (WordPress.org Edition) =

* **Evidence-Based GEO Readiness Audit (Princeton Research Backed):**
  * **BLUF Check (Bottom Line Up Front):** Ensures your opening 150 words contain a direct, concise answer to improve LLM retrieval scores.
  * **Statistical Fact Density:** Measures verified statistics, percentages, and benchmark metrics that increase quotation likelihood.
  * **Heading & Table Structure:** Verifies clean H2/H3 hierarchy, bullet lists, and comparison tables.
  * **E-E-A-T & Citation Authority:** Validates external reference sources, author credentials, and `dateModified` freshness timestamps.
  * **Machine-Readable Schema Diagnostics:** Automatically validates and generates JSON-LD Schema (`Article`, `WebPage`, `Product`, `FAQPage`, `SpeakableSpecification`, `BreadcrumbList`, Author E-E-A-T, and `citation` arrays).
* **Community-Standard /llms.txt & v2 Discovery Engine:** Automatically generates `/llms.txt` and `/llms-full.txt` at your domain root using native WordPress rewrite rules, complete with `llmstxt.org` v2 `<link rel="describedby">` and HTTP `Link:` discovery headers.
* **Clean Markdown Output (?format=markdown & .md URLs):** Strips away navigation menus, footers, forms, scripts, and ads to serve lightweight, token-efficient Markdown content via both `?format=markdown` and `.md` URL extensions (e.g., `/your-post.md`).
* **AI Key Takeaways (TL;DR) Box & Shortcode:** Add a high-conversion BLUF summary to any post or page via the editor meta box or `[nexura_ai_summary]` shortcode—automatically injected into `/llms.txt`, `/llms-full.txt`, and `SpeakableSpecification` schema.
* **IndexNow Instant AI Indexing Protocol:** Automatically pings IndexNow (`api.indexnow.org`) to notify Bing, Microsoft Copilot, and ChatGPT Search the moment you publish or update content, complete with virtual API key verification.
* **2026 Smart AI Crawler Permissions (Search Engines, Live User Agents & Scrapers):**
  * **Allow Search Visibility:** Allow `OAI-SearchBot` (ChatGPT Search), `Claude-SearchBot`, `PerplexityBot`, `Googlebot`, `Applebot`, `DuckAssistBot`, `Amazonbot`, and `YouBot`.
  * **Allow Live User-Triggered Fetchers:** Dedicated controls for `ChatGPT-User`, `Claude-User`, and `Perplexity-User` when real users paste your links into AI chats.
  * **Protect Original Content:** Optionally block data-harvesting training scrapers like `GPTBot`, `ClaudeBot`, `Google-Extended`, `Applebot-Extended`, `Meta-ExternalAgent`, and `Bytespider`.
* **AI Crawler, WAF & Physical robots.txt Health Check:** Built-in dashboard diagnostics detect physical `robots.txt` conflicts, WordPress "Discourage search engines" settings, and Cloudflare Bot Fight Mode considerations.
* **Individual Page & Post Exclusion:** Easily exclude any specific post or page from `/llms.txt`, AI search engine access, and GEO scanning via post search autocomplete or the editor meta box.
* **Ultra-Simple 60-Second Setup Wizard:** Guided native onboarding with an instant **"1-Click Express Setup"** option for non-technical bloggers.
* **100% WordPress Native UI/UX:** Built strictly with WordPress admin design standards (`.wrap`, `.form-table`, `.card`, Dashicons). Zero external CSS bloat, zero Tailwind, zero Bootstrap, and full support for WordPress Admin Color Schemes & Dark Mode.
* **Performance & Privacy Focused:** Local processing with transient caching and request-level query memoization.

= Free vs Pro Feature Comparison =

The core version of Nexura AI Search Optimizer is 100% free, forever, and fully functional on unlimited posts. [Upgrade to Nexura Pro](https://checkout.freemius.com/plugin/42525/plan/73995/) for advanced bulk workflows, automated scans, and direct AI agent integration:

* **Princeton GEO Readiness Audit:** Free: Single Posts & Pages | Pro: Single + Bulk Library Scan
* **Community-Standard /llms.txt & /llms-full.txt:** Free: Unlimited | Pro: Unlimited
* **Clean Markdown (?format=markdown & .md URLs):** Free: Unlimited | Pro: Unlimited
* **AI Key Takeaways Box ([nexura_ai_summary]):** Free: Included | Pro: Included
* **IndexNow Instant Indexing (Copilot & ChatGPT):** Free: Included | Pro: Included + Auto-Ping on AI Rewrite
* **2026 AI Crawler Matrix (14+ Bots):** Free: Full Control | Pro: Full Control
* **Individual Post & Page Exclusions:** Free: Unlimited | Pro: Unlimited
* **1-Click AI Fix Prompt Generator:** Free: Included (Manual Copy) | Pro: Included (Manual Copy)
* **60-Second Onboarding Wizard:** Free: Included | Pro: Included
* **1-Click AI Content Rewriter (BYOK):** Free: Not Available | Pro: Included (OpenAI GPT-4.1/4o, Claude Sonnet 4/3.7, Gemini 2.5)
* **Site-Wide Bulk GEO Library Scanner:** Free: Not Available | Pro: Included (Batch Background Scan)
* **WooCommerce AI Shopping Optimizer:** Free: Basic | Pro: Advanced (Specs, Stock, Prices)
* **Live AI Crawler Hit & Human AI Referral Analytics:** Free: Not Available | Pro: Included
* **Automated Weekly Audits & Email Alerts:** Free: Not Available | Pro: Included
* **Printable PDF & Formula-Safe CSV Export:** Free: Single Post PDF | Pro: Bulk + Single PDF & CSV
* **Customer Support:** Free: WordPress.org Community | Pro: Priority Engineering Support

= What Nexura Pro Adds (Powered by Freemius) =

For professional bloggers, agencies, and e-commerce websites managing growing libraries, **Nexura Pro** ([Get Nexura Pro Here](https://checkout.freemius.com/plugin/42525/plan/73995/)) adds powerful automation and bulk management tools:

* **1-Click AI Automated Content Rewriter (BYOK):** Connect your own OpenAI (`gpt-4.1`, `gpt-4.1-mini`, `gpt-4o`, `gpt-4o-mini`), Anthropic (`claude-sonnet-4-20250514`, `claude-3-7-sonnet-latest`, `claude-3-5-sonnet-latest`), or Google (`gemini-2.5-flash`, `gemini-2.5-pro`, `gemini-2.0-flash`) API key. Follows the honest **Bring Your Own Key (BYOK)** model so you pay direct wholesale provider prices (pennies per article) with zero markup or expensive token subscriptions.
* **Site-Wide Bulk GEO Audit:** Scan every post, page, and custom post type across your entire site via paginated background AJAX without server timeouts.
* **Automated WP-Cron Scheduler:** Automatically runs weekly audits and alerts you via email when published content loses AI search citation readiness.
* **Formula-Safe CSV & PDF Export:** Export comprehensive white-label audit reports for team review or client presentations, safeguarded against CSV formula injection.
* **WooCommerce AI Shopping Optimization:** Automatically feeds high-density product specifications, real-time stock status, SKU, and price schema into your `/llms.txt` and Markdown output.
* **AI Bot Traffic & Human AI Referral Analytics:** Track real-time visits from 15+ AI crawlers alongside human visitor referral clicks coming from `chatgpt.com`, `perplexity.ai`, `claude.ai`, `gemini.google.com`, `copilot.microsoft.com`, and `you.com`.
* **Priority Email & Ticket Support:** Direct assistance from our WordPress engineering team.

= External Services =

This plugin connects to external services in specific scenarios:

1. **Freemius SDK (Commercial Licensing & Automatic Updates):**
   * **Service Provider:** Freemius, Inc. (https://freemius.com/)
   * **Checkout & Upgrade URL:** https://checkout.freemius.com/plugin/42525/plan/73995/
   * **Terms of Service:** https://freemius.com/terms/
   * **Privacy Policy:** https://freemius.com/privacy/
   * **Data Transmitted:** Site URL, WordPress version, PHP version, active plugins list, and plugin version (transmitted only when the user explicitly opts in or activates a commercial license).
   * **User Control:** The opt-in screen after activation is completely optional. Users can click "Skip" at any time and use all free features without restriction.

2. **IndexNow Protocol (Instant Search & AI Engine Indexing):**
   * **Service Endpoint:** https://api.indexnow.org/indexnow
   * **Documentation & Terms:** https://www.indexnow.org/terms
   * **Data Transmitted:** When a public post, page, or product is published or updated (and IndexNow is enabled in Advanced Settings), your site's hostname, generated IndexNow verification key, and the public permalink URL of the updated post are sent via a non-blocking ping to `api.indexnow.org`. No personal or visitor data is transmitted.
   * **User Control:** Can be enabled or disabled at any time under **AI Search Optimizer > Advanced > IndexNow Instant AI Indexing**.

3. **Third-Party AI APIs (Pro Feature - Bring Your Own Key):**
   * If you configure an API key in the optional Pro AI Content Rewriter, content optimization prompts are sent directly from your WordPress server to your chosen provider:
     * OpenAI API: https://openai.com/policies/terms-of-use / https://openai.com/privacy/
     * Anthropic Claude API: https://www.anthropic.com/legal/commercial-terms / https://www.anthropic.com/legal/privacy
     * Google Gemini API: https://ai.google.dev/gemini-api/terms
   * No user content or API keys are ever sent to any third-party intermediary or Nexura servers. Direct encrypted SSL API calls occur solely between your server and the chosen provider.

== Installation ==

1. Log into your WordPress admin dashboard.
2. Navigate to **Plugins > Add New** and search for `Nexura AI Search Optimizer`.
3. Click **Install Now**, then click **Activate**.
4. The 5-step Setup Wizard will launch automatically:
   * Click **"Activate Best-Practice Defaults & Finish"** for instant 10-second setup, or
   * Step through the 5 guided screens to customize which post types to include and manage AI bot rules.
5. Your `/llms.txt` is instantly live at `https://yourdomain.com/llms.txt`!

== Frequently Asked Questions ==

= Will this plugin conflict with Yoast SEO, Rank Math, or All in One SEO? =

No, absolutely not. Nexura includes an automated "SEO Compatibility Mode". If you have Yoast SEO, Rank Math, AIOSEO, or SEOPress active, Nexura detects their schema and will not inject duplicate structured data. Yoast and Rank Math manage your traditional Google search presence (`sitemap.xml`), while Nexura manages your generative AI search presence (`llms.txt`).

= Do I still need Yoast SEO or Rank Math if I use Nexura? =

Yes, we strongly recommend keeping them! Combining Yoast or Rank Math with Nexura provides a comprehensive search strategy: traditional organic search optimization + generative AI search readiness.

= What is the difference between OAI-SearchBot, ChatGPT-User, and GPTBot? =

This is a critical distinction that many site owners miss:
* `OAI-SearchBot` is OpenAI's search crawler. It indexes your content specifically for ChatGPT Search so that users get clickable citation links back to your site.
* `ChatGPT-User` is triggered in real-time when a human user asks ChatGPT to browse or summarize a URL on your site.
* `GPTBot` is OpenAI's training scraper. It downloads your content to train future foundation models.
Nexura allows you to keep `OAI-SearchBot` and `ChatGPT-User` allowed (to get search traffic and citations) while giving you the option to block `GPTBot` (to protect your original content).

= Does Nexura work with caching plugins like WP Rocket or LiteSpeed? =

Yes. Nexura automatically invalidates its virtual cache whenever you publish or update a post. It also sends standard HTTP `Cache-Control` headers so that external caching layers (such as LiteSpeed, WP Rocket, or Cloudflare) know when to refresh your `/llms.txt` and Markdown endpoints.

= What is /llms.txt and why is it important? =

`/llms.txt` is an open community standard (inspired by `robots.txt` and `sitemap.xml`) created to provide AI models and agents with a concise, clean summary of your website's content in Markdown format. It helps AI agents navigate your site efficiently without wasting tokens parsing complex HTML layouts.

= Does Nexura modify my physical robots.txt file? =

No. Nexura uses WordPress's native `robots_txt` filter hook to inject virtual directives cleanly. It will never overwrite or corrupt your physical server files.

= Does this plugin slow down my website? =

Not at all. Nexura processes everything locally and caches output dynamically using WordPress transients. Queries are bounded and indexed, and no external tracking or blocking APIs run on frontend page loads.

= How does the Bring Your Own Key (BYOK) AI Agent work in Pro? =

Nexura Pro connects directly to your OpenAI, Anthropic Claude, or Google Gemini API key. Unlike services that charge high recurring monthly subscription fees for AI rewriting, BYOK lets you pay the AI provider directly at wholesale API rates (averaging just $0.01 - $0.03 per article rewrite).

= Can I use Nexura with WooCommerce? =

Yes! In the free version, WooCommerce products can be audited and included in `/llms.txt`. With Nexura Pro, advanced AI shopping specifications, live inventory statuses, and pricing schema are automatically formatted for AI shopping assistants.

= How do I upgrade to Nexura Pro? =

You can upgrade directly via our secure Freemius checkout at https://checkout.freemius.com/plugin/42525/plan/73995/ or from your WordPress dashboard by clicking the "Upgrade to Pro" button inside the plugin settings.

== Screenshots ==

1. Dashboard Overview showing site AI health status, live /llms.txt link, and quick GEO audit widget.
2. Friendly 5-step Setup Wizard with 1-click recommended settings option.
3. AI Crawler Matrix for granular control over search crawlers, live user fetchers, and training scrapers.
4. On-Page GEO Readiness Audit with Princeton-backed scoring and machine-readable schema diagnostics.
5. LLMs.txt post type selector and custom preamble configuration.

== Changelog ==

= 1.1.0 =
* Added: `llmstxt.org` v2 HTTP `Link:` discovery headers (`rel="describedby"; type="text/plain"` and `rel="alternate"; type="text/markdown"`) and `.md` URL extension mirror (`/post-slug.md`).
* Added: IndexNow Instant Indexing Protocol (`api.indexnow.org`) with automatic virtual key verification and instant pings for Bing, Microsoft Copilot, and ChatGPT Search.
* Added: `[nexura_ai_summary]` shortcode and Post Editor Meta Box controls for top-of-post AI Key Takeaways (TL;DR) boxes, integrated into `/llms.txt`, `/llms-full.txt`, and `SpeakableSpecification` schema.
* Added: Automatic `FAQPage`, `SpeakableSpecification`, Author E-E-A-T (`description`, `url`, `sameAs`), and external `citation` JSON-LD Schema extraction.
* Added: 2026 Live User-Triggered AI Browsing Agent controls (`ChatGPT-User`, `Claude-User`, `Perplexity-User`) and extended search/training bot directives (`DuckAssistBot`, `Amazonbot`, `YouBot`, `Meta-ExternalAgent`).
* Added: Human AI Referral Traffic Tracker in Pro Bot Analytics to measure real visitor clicks from ChatGPT, Perplexity, Claude, Gemini, Microsoft Copilot, and You.com.
* Added: 2026 AI Models in Pro AI Agent (`gemini-2.5-flash`, `gemini-2.5-pro`, `gemini-2.0-flash`, `gpt-4.1`, `gpt-4.1-mini`, `claude-sonnet-4-20250514`, `claude-3-7-sonnet-latest`) with automatic migration for retired Gemini 1.5 models.
* Added: AI Crawler, WAF & Physical `robots.txt` Health Check diagnostics card on the Dashboard.
* Improved: Live synchronization of Gutenberg Block Editor and Classic TinyMCE Editor when applying 1-Click AI Rewrites from the Post Edit screen.
* Improved: Request-level static caching for excluded utility page lookups, eliminating N+1 database queries on admin list tables.
* Fixed: Virtual `robots.txt` syntax compliance (`User-agent: *`), Bot Analytics hook priority on `/llms.txt` and `.md` endpoints, WooCommerce plugin load-order hook registration, and Editor/Author role capabilities for on-page GEO scans.

= 1.0.0 =
* Initial public release on WordPress.org.
* Native rewrite rules for `/llms.txt` and `/llms-full.txt`.
* Clean Markdown output endpoint via `?format=markdown`.
* Evidence-based on-page GEO readiness audit engine with Princeton academic scoring metrics.
* 2026 AI Bot Permission Matrix (`OAI-SearchBot`, `GPTBot`, `Claude-SearchBot`, `PerplexityBot`, `Google-Extended`).
* Deep compatibility mode for Yoast SEO, Rank Math, and All in One SEO.
* WordPress Native 5-step Setup Wizard with 1-click Express Setup.
* Full integration with Freemius SDK for secure, seamless Pro upgrades.

== Upgrade Notice ==

= 1.1.0 =
Major 2026 GEO Update: Adds llmstxt.org v2 Link headers & .md URLs, IndexNow instant AI indexing, [nexura_ai_summary] Key Takeaways boxes, auto FAQPage/Speakable Schema, and 26 stability fixes.

= 1.0.0 =
Welcome to Nexura AI Search Optimizer! Optimize your WordPress site for ChatGPT Search, Claude, Perplexity, and Google AI Overviews today.
