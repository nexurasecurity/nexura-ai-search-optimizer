<?php
/**
 * Plugin Rewrite Rules & Endpoint Interceptor
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles custom rewrite endpoints for /llms.txt, /llms-full.txt, and Markdown formats.
 */
class Rewrite_Rules {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'add_rules' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'intercept_request' ), 1 );
	}

	/**
	 * Add custom rewrite rules.
	 *
	 * @return void
	 */
	public function add_rules() {
		add_rewrite_rule( '^llms\.txt$', 'index.php?naso_llms_txt=1', 'top' );
		add_rewrite_rule( '^llms-full\.txt$', 'index.php?naso_llms_full_txt=1', 'top' );
		add_rewrite_rule( '^robots\.txt$', 'index.php?robots=1', 'top' );
	}

	/**
	 * Register query variables with WordPress.
	 *
	 * @param array $vars Existing query vars.
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'naso_llms_txt';
		$vars[] = 'naso_llms_full_txt';
		$vars[] = 'naso_markdown';
		return $vars;
	}

	/**
	 * Intercept incoming requests and serve endpoints before theme templates load.
	 *
	 * @return void
	 */
	public function intercept_request() {
		$uri_path = '';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$raw_path = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$parsed   = wp_parse_url( $raw_path, PHP_URL_PATH );
			if ( $parsed ) {
				$uri_path = trim( $parsed, '/' );
			}
		}

		// Check for /llms.txt
		$is_llms = ( '1' === (string) get_query_var( 'naso_llms_txt' ) ) ||
				( ! empty( $uri_path ) && ( 'llms.txt' === $uri_path || preg_match( '#(^|/)llms\.txt$#i', $uri_path ) ) );

		if ( $is_llms ) {
			if ( ! Settings::get( 'llms_enabled', 1 ) ) {
				wp_die( esc_html__( 'LLMS.txt endpoint is currently disabled by administrator.', 'nexura-ai-search-optimizer' ), 403 );
			}

			require_once NEXURA_AI_SEARCH_DIR . 'includes/class-llms-generator.php';
			$generator = new LLMS_Generator();
			$generator->render( false );
			exit;
		}

		// Check for /llms-full.txt
		$is_llms_full = ( '1' === (string) get_query_var( 'naso_llms_full_txt' ) ) ||
				( ! empty( $uri_path ) && ( 'llms-full.txt' === $uri_path || preg_match( '#(^|/)llms-full\.txt$#i', $uri_path ) ) );

		if ( $is_llms_full ) {
			if ( ! Settings::get( 'llms_full_enabled', 1 ) ) {
				wp_die( esc_html__( 'LLMS-full.txt endpoint is currently disabled by administrator.', 'nexura-ai-search-optimizer' ), 403 );
			}

			require_once NEXURA_AI_SEARCH_DIR . 'includes/class-llms-generator.php';
			$generator = new LLMS_Generator();
			$generator->render( true );
			exit;
		}

		// Check for /robots.txt (essential for subdirectory installs where WordPress core skips rewrite rule).
		$is_robots = ( '1' === (string) get_query_var( 'robots' ) ) ||
				( ! empty( $uri_path ) && ( 'robots.txt' === $uri_path || preg_match( '#(^|/)robots\.txt$#i', $uri_path ) ) );

		if ( $is_robots ) {
			do_robots();
			exit;
		}

		// Check for virtual IndexNow API verification file (/<32-char-hex>.txt)
		if ( ! empty( $uri_path ) && Settings::get( 'indexnow_enabled', 1 ) && preg_match( '#(^|/)([a-f0-9]{32})\.txt$#i', $uri_path, $key_matches ) ) {
			require_once NEXURA_AI_SEARCH_DIR . 'includes/class-indexnow.php';
			$indexnow_key = IndexNow::get_api_key();
			if ( ! empty( $indexnow_key ) && strtolower( $key_matches[2] ) === strtolower( $indexnow_key ) ) {
				status_header( 200 );
				header( 'Content-Type: text/plain; charset=utf-8' );
				header( 'X-Robots-Tag: noindex' );
				echo esc_html( $indexnow_key );
				exit;
			}
		}

		// Check for llmstxt.org v2 .md URL extension mirror (e.g. /post-slug.md or /docs/page.md)
		if ( ! empty( $uri_path ) && Settings::get( 'markdown_output_enabled', 1 ) && preg_match( '#^(.+)\.md$#i', $uri_path, $md_matches ) ) {
			$clean_slug_path = trim( $md_matches[1], '/' );
			$home_path       = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
			if ( ! empty( $home_path ) && 0 === strpos( $clean_slug_path, $home_path . '/' ) ) {
				$clean_slug_path = substr( $clean_slug_path, strlen( $home_path ) + 1 );
			}
			$candidate_url = home_url( '/' . $clean_slug_path . '/' );
			$resolved_id   = url_to_postid( $candidate_url );
			if ( ! $resolved_id ) {
				$base_slug   = basename( $clean_slug_path );
				$found_posts = get_posts( array(
					'name'        => sanitize_title( $base_slug ),
					'post_type'   => Settings::get_supported_post_types(),
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
				) );
				if ( ! empty( $found_posts ) ) {
					$resolved_id = (int) $found_posts[0];
				}
			}

			if ( $resolved_id ) {
				status_header( 200 );
				require_once NEXURA_AI_SEARCH_DIR . 'includes/class-markdown-output.php';
				$md_output = new Markdown_Output();
				$md_output->render_singular( $resolved_id );
				exit;
			}
		}

		// Check for ?format=markdown (public GET parameter)
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$format_param = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : '';
		if ( 'markdown' === $format_param && Settings::get( 'markdown_output_enabled', 1 ) ) {
			if ( is_singular() ) {
				require_once NEXURA_AI_SEARCH_DIR . 'includes/class-markdown-output.php';
				$md_output = new Markdown_Output();
				$md_output->render_singular( get_queried_object_id() );
				exit;
			}
		}
	}
}
