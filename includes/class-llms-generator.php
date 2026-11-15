<?php
/**
 * LLMS.txt and LLMS-full.txt Generator
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates llmstxt.org compliant index and full content files for AI systems.
 */
class LLMS_Generator {

	/**
	 * Request-level static cache for excluded post IDs to avoid N+1 SQL queries.
	 *
	 * @var array|null
	 */
	private static $excluded_ids_cache = null;

	/**
	 * Flush the in-memory excluded post IDs cache.
	 *
	 * @return void
	 */
	public static function flush_excluded_cache() {
		self::$excluded_ids_cache = null;
	}

	/**
	 * Output the llms content directly to output stream.
	 *
	 * @param bool $is_full Whether to output full content or summary index.
	 * @return void
	 */
	public function render( $is_full = false ) {
		$cache_key = $is_full ? 'llms_full_txt' : 'llms_txt';
		$content   = Cache::get( $cache_key );

		if ( false === $content || null === $content ) {
			$content = $this->generate_content( $is_full );
			Cache::set( $cache_key, $content );
		}

		$ttl_hours = absint( Settings::get( 'cache_ttl_hours', 24 ) );
		$max_age   = $ttl_hours * HOUR_IN_SECONDS;

		// Send proper headers
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: all' );
		header( 'Cache-Control: public, max-age=' . absint( $max_age ) );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text /llms.txt response; escaping with wp_kses_post corrupts Markdown code blocks and angle brackets.
		echo $content;
		exit;
	}

	/**
	 * Decode HTML entities and sanitize for plain text AI consumption.
	 *
	 * @param string $text Raw text.
	 * @param bool   $preserve_indentation Whether to preserve line-leading spaces/tabs for code blocks.
	 * @return string Clean decoded text.
	 */
	public static function clean_plain_text( $text, $preserve_indentation = false ) {
		if ( empty( $text ) ) {
			return '';
		}

		// Normalize ellipses and common snippet endings
		$text = preg_replace( '/\[&hellip;\]|\[\.\.\.\]|&hellip;|\x{2026}/u', '...', $text );

		// Decode all HTML entities (including &#8217;, &amp;, &quot;, &#241;, etc.)
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Normalize special typography quotes and hyphens to ASCII equivalents for token savings
		$search  = array( '&#8217;', '&#8216;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#039;', '&apos;', "\xc2\xa0" );
		$replace = array( "'", "'", '"', '"', '-', '--', "'", "'", ' ' );
		$text    = str_replace( $search, $replace, $text );

		// Normalize multiple whitespace to single space unless preserving code block indentation
		if ( ! $preserve_indentation ) {
			$text = preg_replace( '/[ \t]+/', ' ', $text );
		}

		return trim( $text );
	}

	/**
	 * Get WooCommerce and e-commerce utility page IDs that should be excluded from AI indexing.
	 *
	 * @return array List of post IDs to exclude.
	 */
	public static function get_excluded_post_ids() {
		if ( null !== self::$excluded_ids_cache ) {
			return self::$excluded_ids_cache;
		}

		$excluded_ids = array();

		// WooCommerce native transactional page IDs (Cart, Checkout, My Account)
		if ( function_exists( 'wc_get_page_id' ) ) {
			$wc_pages = array( 'cart', 'checkout', 'myaccount' );
			foreach ( $wc_pages as $page_slug ) {
				$pid = wc_get_page_id( $page_slug );
				if ( $pid > 0 ) {
					$excluded_ids[] = absint( $pid );
				}
			}
		}

		// Direct WooCommerce options fallback
		$wc_options = array( 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id' );
		foreach ( $wc_options as $opt ) {
			$val = (int) get_option( $opt );
			if ( $val > 0 ) {
				$excluded_ids[] = $val;
			}
		}

		// Transactional utility slugs (session endpoints only, NOT legal/trust policies)
		$utility_slugs = array(
			'cart',
			'checkout',
			'my-account',
			'myaccount',
			'order-pay',
			'order-received',
			'lost-password',
			'password-reset',
			'reset-password',
		);
		foreach ( $utility_slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page && isset( $page->ID ) ) {
				$excluded_ids[] = absint( $page->ID );
			}
		}

		// User-configured custom excluded IDs from Settings & postmeta
		$custom_ids_str = Settings::get( 'excluded_post_ids', '' );
		if ( ! empty( $custom_ids_str ) ) {
			$raw_ids = explode( ',', (string) $custom_ids_str );
			foreach ( $raw_ids as $rid ) {
				$rid = absint( trim( $rid ) );
				if ( $rid > 0 ) {
					$excluded_ids[] = $rid;
				}
			}
		}

		// Custom per-post excluded IDs from postmeta and utility pages containing WooCommerce cart/checkout shortcodes or blocks
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$meta_excluded = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_naso_exclude_from_ai' AND meta_value = '1'" );
		$shortcode_excluded = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND (
				post_content LIKE '%[woocommerce_cart%' OR
				post_content LIKE '%[woocommerce_checkout%' OR
				post_content LIKE '%[woocommerce_my_account%' OR
				post_content LIKE '%[woocommerce_order_tracking%' OR
				post_content LIKE '%wp:woocommerce/cart%' OR
				post_content LIKE '%wp:woocommerce/checkout%' OR
				post_content LIKE '%wp:woocommerce/my-account%'
			)"
		);
		// phpcs:enable
		if ( ! empty( $meta_excluded ) ) {
			$excluded_ids = array_merge( $excluded_ids, array_map( 'absint', (array) $meta_excluded ) );
		}
		if ( ! empty( $shortcode_excluded ) ) {
			$excluded_ids = array_merge( $excluded_ids, array_map( 'absint', (array) $shortcode_excluded ) );
		}

		self::$excluded_ids_cache = array_values( array_unique( array_filter( $excluded_ids ) ) );
		return self::$excluded_ids_cache;
	}

	/**
	 * Check if a post is specifically a system or e-commerce transactional utility page
	 * (Cart, Checkout, My Account, Order Tracking) rather than a user-excluded content page.
	 *
	 * @param int|\WP_Post $post Post object or ID.
	 * @return bool
	 */
	public static function is_system_utility_page( $post ) {
		$post_obj = get_post( $post );
		if ( ! $post_obj ) {
			return false;
		}

		if ( function_exists( 'wc_get_page_id' ) ) {
			foreach ( array( 'cart', 'checkout', 'myaccount' ) as $wc_slug ) {
				if ( absint( wc_get_page_id( $wc_slug ) ) === absint( $post_obj->ID ) ) {
					return true;
				}
			}
		}

		foreach ( array( 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id' ) as $opt ) {
			if ( absint( get_option( $opt ) ) === absint( $post_obj->ID ) ) {
				return true;
			}
		}

		$utility_slugs = array(
			'cart',
			'checkout',
			'my-account',
			'myaccount',
			'order-pay',
			'order-received',
			'lost-password',
			'password-reset',
			'reset-password',
		);
		if ( ! empty( $post_obj->post_name ) && in_array( $post_obj->post_name, $utility_slugs, true ) ) {
			return true;
		}

		if ( 'page' === $post_obj->post_type && ! empty( $post_obj->post_content ) ) {
			$content = $post_obj->post_content;
			if (
				has_shortcode( $content, 'woocommerce_cart' ) ||
				has_shortcode( $content, 'woocommerce_checkout' ) ||
				has_shortcode( $content, 'woocommerce_my_account' ) ||
				has_shortcode( $content, 'woocommerce_order_tracking' ) ||
				( function_exists( 'has_block' ) && (
					has_block( 'woocommerce/cart', $post_obj ) ||
					has_block( 'woocommerce/checkout', $post_obj ) ||
					has_block( 'woocommerce/my-account', $post_obj )
				) ) ||
				strpos( $content, 'wp:woocommerce/cart' ) !== false ||
				strpos( $content, 'wp:woocommerce/checkout' ) !== false ||
				strpos( $content, 'wp:woocommerce/my-account' ) !== false
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a specific post is an excluded utility page (e.g. cart, checkout, my-account)
	 * or has been manually excluded by the user.
	 *
	 * Note: Policy and trust pages (Privacy Policy, Refund Policy, Terms) are NOT automatically
	 * excluded because they provide critical E-E-A-T and citation authority for AI search engines.
	 *
	 * @param int|\WP_Post $post Post object or ID.
	 * @return bool True if excluded.
	 */
	public static function is_excluded_utility_page( $post ) {
		$post_obj = get_post( $post );
		if ( ! $post_obj ) {
			return false;
		}

		// Check if user manually marked this post to be excluded
		if ( '1' === get_post_meta( $post_obj->ID, '_naso_exclude_from_ai', true ) ) {
			return true;
		}

		$excluded_ids = self::get_excluded_post_ids();
		if ( in_array( $post_obj->ID, $excluded_ids, true ) ) {
			return true;
		}

		$utility_slugs = array(
			'cart',
			'checkout',
			'my-account',
			'myaccount',
			'order-pay',
			'order-received',
			'lost-password',
			'password-reset',
			'reset-password',
		);
		if ( in_array( $post_obj->post_name, $utility_slugs, true ) ) {
			return true;
		}

		// Check for WooCommerce functional shortcodes or Gutenberg blocks inside content
		if ( 'page' === $post_obj->post_type && ! empty( $post_obj->post_content ) ) {
			$content = $post_obj->post_content;
			if (
				has_shortcode( $content, 'woocommerce_cart' ) ||
				has_shortcode( $content, 'woocommerce_checkout' ) ||
				has_shortcode( $content, 'woocommerce_my_account' ) ||
				has_shortcode( $content, 'woocommerce_order_tracking' ) ||
				( function_exists( 'has_block' ) && (
					has_block( 'woocommerce/cart', $post_obj ) ||
					has_block( 'woocommerce/checkout', $post_obj ) ||
					has_block( 'woocommerce/my-account', $post_obj )
				) ) ||
				strpos( $content, 'wp:woocommerce/cart' ) !== false ||
				strpos( $content, 'wp:woocommerce/checkout' ) !== false
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build text content string.
	 *
	 * @param bool $is_full Whether to include full text.
	 * @return string
	 */
	public function generate_content( $is_full = false ) {
		$site_name = self::clean_plain_text( get_bloginfo( 'name' ) );
		$site_desc = Settings::get( 'site_summary' );
		if ( empty( $site_desc ) ) {
			$site_desc = get_bloginfo( 'description' );
		}
		if ( empty( $site_desc ) ) {
			$site_desc = sprintf( 'Official AI-accessible content index for %s.', $site_name );
		}
		$site_desc = self::clean_plain_text( $site_desc );

		$output  = '# ' . $site_name . "\n\n";
		$output .= '> ' . $site_desc . "\n\n";

		// Guidance note for AI engines
		$output .= "This file provides structured, canonical markdown documentation for AI agents, LLMs, and search crawlers.\n\n";

		$post_types = Settings::get_supported_post_types();
		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		$max_links        = absint( Settings::get( 'max_links', 100 ) );
		$include_excerpts = (bool) Settings::get( 'include_excerpts', 1 );

		// Exclude WooCommerce Cart, Checkout, My Account utility pages in post loop
		$excluded_ids = self::get_excluded_post_ids();
		$fetch_limit  = ! empty( $excluded_ids ) ? ( $max_links + count( $excluded_ids ) ) : $max_links;

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => $fetch_limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		$query = new \WP_Query( $args );

		$output .= "## Main Content & Documentation\n\n";

		if ( $query->have_posts() ) {
			$count = 0;
			while ( $query->have_posts() ) {
				$query->the_post();

				// Double-check utility slug exclusion
				if ( self::is_excluded_utility_page( get_the_ID() ) ) {
					continue;
				}

				$post_title = self::clean_plain_text( get_the_title() );
				$permalink  = get_permalink();

				if ( $include_excerpts ) {
					$ai_summary    = get_post_meta( get_the_ID(), '_naso_ai_summary', true );
					$raw_excerpt   = ! empty( $ai_summary ) ? $ai_summary : get_the_excerpt();
					$clean_excerpt = self::clean_plain_text( wp_strip_all_tags( $raw_excerpt ) );

					if ( ! empty( $clean_excerpt ) ) {
						$output .= sprintf( "- [%s](%s): %s\n", $post_title, $permalink, $clean_excerpt );
					} else {
						$output .= sprintf( "- [%s](%s)\n", $post_title, $permalink );
					}
				} else {
					$output .= sprintf( "- [%s](%s)\n", $post_title, $permalink );
				}

				$count++;
				if ( $count >= $max_links ) {
					break;
				}
			}
			wp_reset_postdata();
		} else {
			$output .= "- [" . $site_name . "](" . home_url( '/' ) . ")\n";
		}

		$output .= "\n## Optional\n\n";
		$output .= "- [Sitemap](" . home_url( '/wp-sitemap.xml' ) . "): XML sitemap index\n";
		$output .= "- [Home](" . home_url( '/' ) . "): Homepage\n";

		// If full text requested, append top published contents in clean markdown
		if ( $is_full ) {
			$output .= "\n\n---\n\n";
			$output .= "# Full Documentation & Published Articles\n\n";

			require_once NEXURA_AI_SEARCH_DIR . 'includes/class-markdown-output.php';
			$md_converter = new Markdown_Output();

			// Limit full text to top 30 posts to avoid memory or token runaway
			$max_full_posts   = min( 30, $max_links );
			$full_fetch_limit = ! empty( $excluded_ids ) ? ( $max_full_posts + count( $excluded_ids ) ) : $max_full_posts;

			$full_args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => $full_fetch_limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			);

			$full_query = new \WP_Query( $full_args );
			if ( $full_query->have_posts() ) {
				$full_count = 0;
				while ( $full_query->have_posts() ) {
					$full_query->the_post();

					if ( self::is_excluded_utility_page( get_the_ID() ) ) {
						continue;
					}

					$post = get_post();

					$clean_post_title = self::clean_plain_text( get_the_title() );
					$output          .= '## ' . $clean_post_title . "\n\n";
					$output          .= '**URL:** ' . get_permalink() . "\n";
					$output          .= '**Date:** ' . get_the_date( 'Y-m-d' ) . "\n\n";

					$full_ai_summary = get_post_meta( $post->ID, '_naso_ai_summary', true );
					if ( ! empty( $full_ai_summary ) ) {
						$output .= '> **Key Takeaways:** ' . self::clean_plain_text( wp_strip_all_tags( $full_ai_summary ) ) . "\n\n";
					}

					$content_markdown = $md_converter->convert_html_to_markdown( $post->post_content );
					$output          .= $content_markdown . "\n\n";
					$output          .= "---\n\n";

					$full_count++;
					if ( $full_count >= $max_full_posts ) {
						break;
					}
				}
				wp_reset_postdata();
			}
		}

		return $output;
	}
}
