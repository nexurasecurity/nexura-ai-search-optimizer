<?php
/**
 * Plugin Settings Management
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin options, defaults, and sanitization.
 */
class Settings {

	/**
	 * Settings group name.
	 */
	const OPTION_GROUP = 'naso_settings_group';

	/**
	 * Option key stored in wp_options.
	 */
	const OPTION_NAME = 'naso_settings';

	/**
	 * Cached settings.
	 *
	 * @var array|null
	 */
	private static $settings = null;

	/**
	 * Get default configuration values.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			// LLMS.txt Configuration
			'llms_enabled'             => 1,
			'llms_full_enabled'        => 1,
			'post_types'               => array( 'post', 'page' ),
			'include_excerpts'         => 1,
			'site_summary'             => '',
			'max_links'                => 100,
			'excluded_post_ids'        => '',

			// Markdown Output
			'markdown_output_enabled'  => 1,
			'markdown_head_link'       => 1,

			// Cache Settings
			'cache_ttl_hours'          => 24,

			// AI Crawler Permissions (Search Visibility vs Model Training Scrapers)
			'crawler_policy_enabled'   => 1,
			'allow_oai_searchbot'      => 1, // OpenAI Search (ChatGPT Search)
			'allow_claude_searchbot'   => 1, // Claude Search
			'allow_perplexity'         => 1, // Perplexity AI
			'allow_googlebot'          => 1, // Google AI Overviews & Search
			'allow_applebot'           => 1, // Apple Intelligence

			'block_gptbot'             => 0, // Model training scraper
			'block_claudebot'          => 0, // Model training scraper
			'block_google_extended'    => 0, // Gemini training (Note: does not block AI Overviews)
			'block_applebot_extended'  => 0, // Apple AI training
			'block_bytespider'         => 1, // ByteDance aggressive scraper

			// Google AI Overviews Snippet Controls
			'geo_snippet_controls'     => 0,
			'max_snippet_length'       => -1, // -1 = unrestricted

			// Data retention
			'delete_data_on_uninstall' => 0,

			// Onboarding Flag
			'onboarding_completed'     => 0,
		);
	}

	/**
	 * Register settings with WordPress Settings API.
	 *
	 * @return void
	 */
	public function register() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::get_defaults(),
			)
		);
	}

	/**
	 * Retrieve all settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_all() {
		if ( null !== self::$settings ) {
			return self::$settings;
		}

		$options = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		self::$settings = wp_parse_args( $options, self::get_defaults() );
		return self::$settings;
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Optional default fallback.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = self::get_all();
		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}
		return $default;
	}

	/**
	 * Get all supported post types including WooCommerce products if active.
	 *
	 * @return array
	 */
	public static function get_supported_post_types() {
		$types = (array) self::get( 'post_types', array( 'post', 'page' ) );
		if ( ( post_type_exists( 'product' ) || class_exists( 'WooCommerce' ) ) && ! in_array( 'product', $types, true ) ) {
			$types[] = 'product';
		}
		return array_values( array_unique( $types ) );
	}

	/**
	 * Check if the active installation has an active Pro license via Freemius.
	 *
	 * @return bool True if active Pro license exists.
	 */
	public static function is_pro() {
		if ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) ) {
			return (bool) naso_fs()->can_use_premium_code();
		}
		return false;
	}

	/**
	 * Update all or subset of settings.
	 *
	 * @param array $new_settings Settings to update.
	 * @return bool
	 */
	public static function update_all( array $new_settings ) {
		$current = self::get_all();
		$merged  = wp_parse_args( $new_settings, $current );
		$sanitized = ( new self() )->sanitize( $merged );
		self::$settings = $sanitized;
		return update_option( self::OPTION_NAME, $sanitized );
	}

	/**
	 * Sanitize submitted settings array.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized array.
	 */
	public function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		$current = get_option( self::OPTION_NAME, self::get_defaults() );
		if ( ! is_array( $current ) ) {
			$current = self::get_defaults();
		}
		$clean = $current;

		$tab = isset( $input['_naso_tab'] ) ? sanitize_key( $input['_naso_tab'] ) : '';

		if ( 'llms' === $tab ) {
			// Only update LLMS tab fields so other tabs are not overwritten.
			$clean['llms_enabled']      = ! empty( $input['llms_enabled'] ) ? 1 : 0;
			$clean['llms_full_enabled'] = ! empty( $input['llms_full_enabled'] ) ? 1 : 0;
			$clean['include_excerpts']  = ! empty( $input['include_excerpts'] ) ? 1 : 0;

			if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
				$clean['post_types'] = array_values( array_filter( array_map( 'sanitize_key', $input['post_types'] ) ) );
				if ( empty( $clean['post_types'] ) ) {
					$clean['post_types'] = array( 'post', 'page' );
				}
			} else {
				$clean['post_types'] = array( 'post', 'page' );
			}

			$clean['site_summary']      = isset( $input['site_summary'] ) ? sanitize_textarea_field( $input['site_summary'] ) : '';
			$max_links                  = isset( $input['max_links'] ) ? absint( $input['max_links'] ) : 100;
			$clean['max_links']         = max( 10, min( 500, $max_links ) );

			$clean['excluded_post_ids'] = isset( $input['excluded_post_ids'] ) ? sanitize_text_field( $input['excluded_post_ids'] ) : '';

		} elseif ( 'crawlers' === $tab ) {
			// Only update Crawlers tab fields.
			$crawler_booleans = array(
				'crawler_policy_enabled',
				'allow_oai_searchbot',
				'allow_claude_searchbot',
				'allow_perplexity',
				'allow_googlebot',
				'allow_applebot',
				'block_gptbot',
				'block_claudebot',
				'block_google_extended',
				'block_applebot_extended',
				'block_bytespider',
				'geo_snippet_controls',
			);
			foreach ( $crawler_booleans as $key ) {
				$clean[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
			}

			if ( isset( $input['max_snippet_length'] ) ) {
				$val = intval( $input['max_snippet_length'] );
				$clean['max_snippet_length'] = ( $val < -1 ) ? -1 : $val;
			}

		} elseif ( 'advanced' === $tab ) {
			// Only update Advanced tab fields.
			if ( isset( $input['cache_ttl_hours'] ) ) {
				$ttl = absint( $input['cache_ttl_hours'] );
				$clean['cache_ttl_hours'] = max( 1, min( 168, $ttl ) );
			}
			$clean['markdown_output_enabled']  = ! empty( $input['markdown_output_enabled'] ) ? 1 : 0;
			$clean['markdown_head_link']       = ! empty( $input['markdown_head_link'] ) ? 1 : 0;
			$clean['delete_data_on_uninstall'] = ! empty( $input['delete_data_on_uninstall'] ) ? 1 : 0;

		} else {
			// Full form / Setup Wizard / update_all fallback.
			$boolean_keys = array(
				'llms_enabled',
				'llms_full_enabled',
				'include_excerpts',
				'markdown_output_enabled',
				'markdown_head_link',
				'crawler_policy_enabled',
				'allow_oai_searchbot',
				'allow_claude_searchbot',
				'allow_perplexity',
				'allow_googlebot',
				'allow_applebot',
				'block_gptbot',
				'block_claudebot',
				'block_google_extended',
				'block_applebot_extended',
				'block_bytespider',
				'geo_snippet_controls',
				'delete_data_on_uninstall',
				'onboarding_completed',
			);
			foreach ( $boolean_keys as $key ) {
				if ( array_key_exists( $key, $input ) ) {
					$clean[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
				}
			}

			if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
				$clean['post_types'] = array_values( array_filter( array_map( 'sanitize_key', $input['post_types'] ) ) );
			}
			if ( isset( $input['site_summary'] ) ) {
				$clean['site_summary'] = sanitize_textarea_field( $input['site_summary'] );
			}
			if ( isset( $input['max_links'] ) ) {
				$clean['max_links'] = max( 10, min( 500, absint( $input['max_links'] ) ) );
			}
			if ( isset( $input['cache_ttl_hours'] ) ) {
				$clean['cache_ttl_hours'] = max( 1, min( 168, absint( $input['cache_ttl_hours'] ) ) );
			}
			if ( isset( $input['max_snippet_length'] ) ) {
				$val = intval( $input['max_snippet_length'] );
				$clean['max_snippet_length'] = ( $val < -1 ) ? -1 : $val;
			}
		}

		self::$settings = $clean;

		// Invalidate cache whenever settings are saved.
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-cache.php';
		Cache::flush_all();

		return $clean;
	}
}
