<?php
/**
 * Plugin Deactivator
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin deactivation routines.
 */
class Deactivator {

	/**
	 * Run deactivation logic.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// 1. Delete transient caches.
		delete_transient( 'naso_llms_txt' );
		delete_transient( 'naso_llms_full_txt' );
		delete_transient( 'naso_activation_redirect' );

		// 2. Flush rewrite rules to remove custom endpoints cleanly.
		flush_rewrite_rules();
	}
}
