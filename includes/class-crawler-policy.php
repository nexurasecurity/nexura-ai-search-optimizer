<?php
/**
 * AI Crawler Policy & Virtual Robots.txt Handler
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates custom crawler directives and meta tags separating Search Bots from Training Scrapers.
 */
class Crawler_Policy {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( Settings::get( 'crawler_policy_enabled', 1 ) ) {
			add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 10, 2 );
		}
		add_action( 'wp_head', array( $this, 'output_meta_snippet_controls' ), 1 );
	}

	/**
	 * Filter virtual robots.txt content with 2026 AI Search Engine directives.
	 *
	 * @param string $output Default robots.txt content.
	 * @param bool   $public Whether the site is public.
	 * @return string Modified robots.txt.
	 */
	public function filter_robots_txt( $output, $public ) {
		$directives  = "\n# -----------------------------------------------------\n";
		$directives .= "# Nexura AI Search Optimizer - AI Search Engine Policy\n";
		$directives .= "# -----------------------------------------------------\n\n";

		// 1. Search Visibility Bots (Allow for ChatGPT Search, Claude, Perplexity citations)
		if ( Settings::get( 'allow_oai_searchbot', 1 ) ) {
			$directives .= "User-agent: OAI-SearchBot\nAllow: /\n\n";
		}

		if ( Settings::get( 'allow_claude_searchbot', 1 ) ) {
			$directives .= "User-agent: Claude-SearchBot\nAllow: /\n\n";
		}

		if ( Settings::get( 'allow_perplexity', 1 ) ) {
			$directives .= "User-agent: PerplexityBot\nAllow: /\n\n";
		}

		if ( Settings::get( 'allow_googlebot', 1 ) ) {
			$directives .= "User-agent: Googlebot\nAllow: /\n\n";
		}

		if ( Settings::get( 'allow_applebot', 1 ) ) {
			$directives .= "User-agent: Applebot\nAllow: /\n\n";
		}

		if ( Settings::get( 'allow_chatgpt_user', 1 ) ) {
			$directives .= "# Live User-Triggered AI Browsing Agents\n";
			$directives .= "User-agent: ChatGPT-User\nAllow: /\n\n";
			$directives .= "User-agent: Claude-User\nAllow: /\n\n";
			$directives .= "User-agent: Perplexity-User\nAllow: /\n\n";
		}

		if ( Settings::get( 'allow_duckassist', 1 ) ) {
			$directives .= "User-agent: DuckAssistBot\nAllow: /\n\n";
			$directives .= "User-agent: Amazonbot\nAllow: /\n\n";
			$directives .= "User-agent: YouBot\nAllow: /\n\n";
		}

		// 2. Training Scrapers (Optional selective blocking of bulk model trainers)
		if ( Settings::get( 'block_gptbot', 0 ) ) {
			$directives .= "User-agent: GPTBot\nDisallow: /\n\n";
		}

		if ( Settings::get( 'block_claudebot', 0 ) ) {
			$directives .= "User-agent: ClaudeBot\nDisallow: /\n\n";
		}

		if ( Settings::get( 'block_google_extended', 0 ) ) {
			$directives .= "# Blocks Gemini model training (Does NOT block Google AI Overviews)\n";
			$directives .= "User-agent: Google-Extended\nDisallow: /\n\n";
		}

		if ( Settings::get( 'block_applebot_extended', 0 ) ) {
			$directives .= "User-agent: Applebot-Extended\nDisallow: /\n\n";
		}

		if ( Settings::get( 'block_meta_external', 0 ) ) {
			$directives .= "User-agent: Meta-ExternalAgent\nDisallow: /\n\n";
		}

		if ( Settings::get( 'block_bytespider', 1 ) ) {
			$directives .= "User-agent: Bytespider\nDisallow: /\n\n";
			$directives .= "User-agent: cohere-ai\nDisallow: /\n\n";
		}

		// 3. Exclude dynamic / checkout / account utility pages & allow LLMS.txt for all agents
		$directives .= "# Global utility exclusions & AI documentation endpoints\n";
		$directives .= "User-agent: *\n";
		$directives .= "Disallow: /cart/\n";
		$directives .= "Disallow: /checkout/\n";
		$directives .= "Disallow: /my-account/\n";

		if ( Settings::get( 'llms_enabled', 1 ) ) {
			$directives .= "Allow: /llms.txt\n";
		}
		if ( Settings::get( 'llms_full_enabled', 1 ) ) {
			$directives .= "Allow: /llms-full.txt\n";
		}
		if ( Settings::get( 'llms_enabled', 1 ) ) {
			$directives .= "\n# LLMs.txt AI Index: " . esc_url( home_url( '/llms.txt' ) ) . "\n";
		}

		return $output . $directives;
	}

	/**
	 * Output snippet control meta tags for Google AI Overviews.
	 *
	 * @return void
	 */
	public function output_meta_snippet_controls() {
		if ( ! Settings::get( 'geo_snippet_controls', 0 ) ) {
			return;
		}

		$max_length = intval( Settings::get( 'max_snippet_length', -1 ) );
		if ( -1 === $max_length ) {
			echo '<meta name="robots" content="max-snippet:-1, max-image-preview:large, max-video-preview:-1" />' . "\n";
		} elseif ( $max_length > 0 ) {
			echo '<meta name="robots" content="max-snippet:' . esc_attr( $max_length ) . ', max-image-preview:large" />' . "\n";
		}
	}
}
