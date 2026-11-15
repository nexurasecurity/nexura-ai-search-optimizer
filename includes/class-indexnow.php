<?php
/**
 * IndexNow Instant Indexing Protocol for Bing, Microsoft Copilot & ChatGPT Search
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles automatic IndexNow key generation, virtual key file verification, and instant URL pings.
 */
class IndexNow {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! Settings::get( 'indexnow_enabled', 1 ) ) {
			return;
		}

		add_action( 'transition_post_status', array( $this, 'on_post_status_transition' ), 20, 3 );
	}

	/**
	 * Retrieve or generate the 32-character hexadecimal IndexNow API key.
	 *
	 * @return string
	 */
	public static function get_api_key() {
		$key = Settings::get( 'indexnow_api_key', '' );
		if ( ! empty( $key ) && preg_match( '/^[a-f0-9]{32}$/i', $key ) ) {
			return $key;
		}

		$key     = md5( home_url() . '|' . wp_generate_password( 32, true, true ) );
		$options = get_option( Settings::OPTION_NAME, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		$options['indexnow_api_key'] = $key;
		update_option( Settings::OPTION_NAME, $options );
		Settings::set_runtime_value( 'indexnow_api_key', $key );

		return $key;
	}

	/**
	 * Trigger IndexNow ping when a supported post is published or updated.
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public function on_post_status_transition( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status || ! ( $post instanceof \WP_Post ) ) {
			return;
		}

		if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
			return;
		}

		$supported_types = Settings::get_supported_post_types();
		if ( ! in_array( $post->post_type, $supported_types, true ) ) {
			return;
		}

		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-llms-generator.php';
		if ( LLMS_Generator::is_excluded_utility_page( $post ) ) {
			return;
		}

		self::ping_post( $post->ID );
	}

	/**
	 * Send an instant non-blocking IndexNow notification for a post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if ping was dispatched.
	 */
	public static function ping_post( $post_id ) {
		if ( ! Settings::get( 'indexnow_enabled', 1 ) ) {
			return false;
		}

		$url = get_permalink( $post_id );
		if ( ! $url ) {
			return false;
		}

		// Throttle duplicate pings for the same post within 60 seconds
		$throttle_key = 'naso_indexnow_ping_' . absint( $post_id );
		if ( get_transient( $throttle_key ) ) {
			return false;
		}
		set_transient( $throttle_key, 1, 60 );

		$key          = self::get_api_key();
		$key_location = home_url( '/' . $key . '.txt' );
		$host         = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( empty( $host ) || in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			// Record local ping state without external call on localhost
			update_option( 'naso_indexnow_last_ping', array(
				'url'    => $url,
				'time'   => current_time( 'mysql' ),
				'status' => 'local_skipped',
			), false );
			return false;
		}

		$payload = array(
			'host'        => $host,
			'key'         => $key,
			'keyLocation' => $key_location,
			'urlList'     => array( $url ),
		);

		wp_remote_post(
			'https://api.indexnow.org/indexnow',
			array(
				'timeout'  => 5,
				'blocking' => false,
				'headers'  => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'     => wp_json_encode( $payload ),
			)
		);

		update_option( 'naso_indexnow_last_ping', array(
			'url'    => $url,
			'time'   => current_time( 'mysql' ),
			'status' => 'sent',
		), false );

		return true;
	}
}
