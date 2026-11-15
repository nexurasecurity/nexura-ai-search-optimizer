<?php
/**
 * Plugin Caching System
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles transient caching for generated llms.txt, audits, and schemas.
 */
class Cache {

	/**
	 * Prefix for all transients.
	 */
	const PREFIX = 'naso_';

	/**
	 * Register automatic cache invalidation hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'save_post', array( $this, 'invalidate_llms_cache' ), 10, 2 );
		add_action( 'deleted_post', array( $this, 'invalidate_llms_cache' ) );
		add_action( 'post_updated', array( $this, 'invalidate_llms_cache' ) );
	}

	/**
	 * Get cached value.
	 *
	 * @param string $key Cache key without prefix.
	 * @return mixed
	 */
	public static function get( $key ) {
		return get_transient( self::PREFIX . $key );
	}

	/**
	 * Set cached value.
	 *
	 * @param string   $key Cache key without prefix.
	 * @param mixed    $value Data to store.
	 * @param int|null $expiration Optional expiration in seconds.
	 * @return bool
	 */
	public static function set( $key, $value, $expiration = null ) {
		if ( null === $expiration ) {
			$ttl_hours  = absint( Settings::get( 'cache_ttl_hours', 24 ) );
			$expiration = $ttl_hours * HOUR_IN_SECONDS;
		}

		return set_transient( self::PREFIX . $key, $value, $expiration );
	}

	/**
	 * Delete a specific cached item.
	 *
	 * @param string $key Cache key without prefix.
	 * @return bool
	 */
	public static function delete( $key ) {
		return delete_transient( self::PREFIX . $key );
	}

	/**
	 * Invalidate all LLMS and audit caches.
	 *
	 * @return void
	 */
	public static function flush_all() {
		self::delete( 'llms_txt' );
		self::delete( 'llms_full_txt' );
	}

	/**
	 * Invalidate cache when posts are saved or updated.
	 *
	 * @param int|null      $post_id Post ID.
	 * @param \WP_Post|null $post Post object.
	 * @return void
	 */
	public function invalidate_llms_cache( $post_id = null, $post = null ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( $post && wp_is_post_revision( $post_id ) ) {
			return;
		}

		self::flush_all();
	}
}
