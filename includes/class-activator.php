<?php
/**
 * Plugin Activator
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation routines.
 */
class Activator {

	/**
	 * Run activation logic.
	 *
	 * @return void
	 */
	public static function activate() {
		// 1. Initialize default settings if not already present.
		$existing = get_option( 'naso_settings' );
		if ( false === $existing || ! is_array( $existing ) ) {
			require_once NEXURA_AI_SEARCH_DIR . 'includes/class-settings.php';
			$defaults = Settings::get_defaults();
			update_option( 'naso_settings', $defaults );
		}

		// 2. Set transient for onboarding wizard redirect (only on single activation, not bulk/CLI).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Standard WordPress activation check.
		if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) && ! wp_doing_cron() && ( ! defined( 'WP_CLI' ) || ! WP_CLI ) ) {
			set_transient( 'naso_activation_redirect', 1, 60 );
		}

		// 3. Register rewrite rules and flush immediately.
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-rewrite-rules.php';
		$rewrite = new Rewrite_Rules();
		$rewrite->add_rules();
		flush_rewrite_rules();

		// 4. Trigger automatic background scan for existing published content (posts, pages, WooCommerce products).
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-background-scanner.php';
		Background_Scanner::trigger_initial_scan();
	}
}
