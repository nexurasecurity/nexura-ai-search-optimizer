<?php
/**
 * Standalone Freemius Pro Release Builder
 *
 * Bundles complete pro/ suite with is_premium => true.
 *
 * @package NexuraAISearchPro
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
