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
