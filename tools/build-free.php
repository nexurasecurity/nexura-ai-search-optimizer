<?php
/**
 * Standalone WordPress.org Free Release Builder
 *
 * Excludes pro/ folder completely for 100% WordPress.org compliance.
 *
 * @package NexuraAISearch
 */

// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	if ( 'cli' === php_sapi_name() ) {
		define( 'ABSPATH', dirname( __DIR__ ) . '/' );
	}
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/deploy-freemius.php';
