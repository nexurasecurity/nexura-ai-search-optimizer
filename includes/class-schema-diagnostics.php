<?php
/**
 * Schema Diagnostics & SEO Plugin Compatibility Engine
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects SEO plugins and inspects JSON-LD structured data for AI search compatibility.
 */
class Schema_Diagnostics {

	/**
	 * Detect active third-party SEO plugins.
	 *
	 * @return array Array with plugin details or empty if none.
	 */
	public static function get_active_seo_plugins() {
		$detected = array();

		// Yoast SEO
		if ( defined( 'WPSEO_VERSION' ) ) {
			$detected[] = array(
				'slug'    => 'yoast-seo',
				'name'    => 'Yoast SEO',
				'version' => WPSEO_VERSION,
				'status'  => 'active',
			);
		}

		// Rank Math
		if ( class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ) ) {
			$version = defined( 'RANK_MATH_VERSION' ) ? RANK_MATH_VERSION : 'Active';
			$detected[] = array(
				'slug'    => 'rank-math',
				'name'    => 'Rank Math SEO',
				'version' => $version,
				'status'  => 'active',
			);
		}

		// All In One SEO
		if ( defined( 'AIOSEO_VERSION' ) ) {
			$detected[] = array(
				'slug'    => 'all-in-one-seo-pack',
				'name'    => 'All in One SEO (AIOSEO)',
				'version' => AIOSEO_VERSION,
				'status'  => 'active',
			);
		}

		// SEOPress
		if ( defined( 'SEOPRESS_VERSION' ) ) {
			$detected[] = array(
				'slug'    => 'wp-seopress',
				'name'    => 'SEOPress',
				'version' => SEOPRESS_VERSION,
				'status'  => 'active',
			);
		}

		// Schema Pro
		if ( defined( 'BSF_AIOSRS_PRO_VER' ) ) {
			$detected[] = array(
				'slug'    => 'schema-pro',
				'name'    => 'Schema Pro',
				'version' => BSF_AIOSRS_PRO_VER,
				'status'  => 'active',
			);
		}

		return $detected;
	}

	/**
	 * Check if any major SEO plugin is handling schema.
	 *
	 * @return bool
	 */
	public static function has_seo_plugin() {
		$plugins = self::get_active_seo_plugins();
		return ! empty( $plugins );
	}

	/**
	 * Inspect raw HTML for JSON-LD schemas and evaluate AI search readiness.
	 *
	 * @param string $html Page HTML.
	 * @return array Diagnostic findings.
	 */
	public function diagnose_html( $html ) {
		$results = array(
			'has_json_ld'       => false,
			'schemas_found'     => array(),
			'missing_types'     => array(),
			'author_declared'   => false,
			'date_modified'     => false,
			'breadcrumbs'       => false,
			'faq_present'       => false,
			'ai_score_impact'   => 0, // out of 15 pts
			'recommendations'   => array(),
		);

		if ( empty( $html ) ) {
			$results['recommendations'][] = __( 'No HTML content available to analyze for schema.', 'nexura-ai-search-optimizer' );
			return $results;
		}

		// Find all <script type="application/ld+json">
		preg_match_all( '#<script[^>]+type=[\'"]application/ld\+json[\'"][^>]*>(.*?)</script>#is', $html, $matches );

		if ( empty( $matches[1] ) ) {
			$active_seo = self::get_active_seo_plugins();
			if ( ! empty( $active_seo ) ) {
				$names = wp_list_pluck( $active_seo, 'name' );
				$results['recommendations'][] = sprintf(
					/* translators: %s: SEO plugin names */
					__( 'Active SEO plugin (%s) detected, but no JSON-LD script was rendered on this specific page.', 'nexura-ai-search-optimizer' ),
					implode( ', ', $names )
				);
			} else {
				$results['recommendations'][] = __( 'No JSON-LD structured data detected. AI search engines rely heavily on schema for citation confidence.', 'nexura-ai-search-optimizer' );
			}
			return $results;
		}

		$results['has_json_ld'] = true;
		$found_types            = array();

		foreach ( $matches[1] as $json_raw ) {
			$data = json_decode( trim( $json_raw ), true );
			if ( ! is_array( $data ) ) {
				continue;
			}

			// Handle @graph container (common in Yoast and Rank Math)
			$items = isset( $data['@graph'] ) && is_array( $data['@graph'] ) ? $data['@graph'] : array( $data );

			foreach ( $items as $item ) {
				if ( ! is_array( $item ) || ! isset( $item['@type'] ) ) {
					continue;
				}

				$type = is_array( $item['@type'] ) ? implode( ', ', $item['@type'] ) : (string) $item['@type'];
				$found_types[] = $type;

				if ( stripos( $type, 'Author' ) !== false || isset( $item['author'] ) ) {
					$results['author_declared'] = true;
				}

				if ( isset( $item['dateModified'] ) || isset( $item['datePublished'] ) ) {
					$results['date_modified'] = true;
				}

				if ( stripos( $type, 'BreadcrumbList' ) !== false ) {
					$results['breadcrumbs'] = true;
				}

				if ( stripos( $type, 'FAQPage' ) !== false ) {
					$results['faq_present'] = true;
				}
			}
		}

		$results['schemas_found'] = array_values( array_unique( $found_types ) );

		// Calculate Schema Score for AI engines (up to 15 points)
		$score = 5; // Base points for having valid JSON-LD
		if ( $results['author_declared'] ) {
			$score += 3;
		}
		if ( $results['date_modified'] ) {
			$score += 3;
		}
		if ( $results['breadcrumbs'] ) {
			$score += 2;
		}
		if ( in_array( 'Article', $results['schemas_found'], true ) || in_array( 'BlogPosting', $results['schemas_found'], true ) || in_array( 'WebPage', $results['schemas_found'], true ) || in_array( 'Product', $results['schemas_found'], true ) ) {
			$score += 2;
		}

		$results['ai_score_impact'] = min( 15, $score );

		// Provide recommendations
		if ( ! $results['author_declared'] ) {
			$results['recommendations'][] = __( 'Declare explicit Author credentials in Schema to boost E-E-A-T citation authority in Perplexity and Claude.', 'nexura-ai-search-optimizer' );
		}
		if ( ! $results['date_modified'] ) {
			$results['recommendations'][] = __( 'Include dateModified in schema so AI search engines know content is fresh.', 'nexura-ai-search-optimizer' );
		}

		return $results;
	}

	/**
	 * Generate GEO-compliant structured data for a post/page/product if not supplied by an SEO plugin.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null Schema array or null on failure.
	 */
	public static function generate_post_schema( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$meta_schema = get_post_meta( $post_id, '_naso_schema_json_ld', true );
		if ( ! empty( $meta_schema ) ) {
			$decoded = json_decode( $meta_schema, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		$title     = get_the_title( $post );
		$url       = get_permalink( $post );
		$author_id = $post->post_author;
		$author    = array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $author_id ) ?: get_bloginfo( 'name' ),
		);
		$date_pub  = get_the_date( 'c', $post );
		$date_mod  = get_the_modified_date( 'c', $post );

		$graph = array();

		if ( 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
			$wc_product = wc_get_product( $post_id );
			$price      = $wc_product ? $wc_product->get_price() : '0.00';
			$sku        = ( $wc_product && $wc_product->get_sku() ) ? $wc_product->get_sku() : 'SKU-' . $post_id;
			$currency   = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';

			$graph[] = array(
				'@type'         => 'Product',
				'@id'           => $url . '#product',
				'name'          => $title,
				'url'           => $url,
				'sku'           => $sku,
				'datePublished' => $date_pub,
				'dateModified'  => $date_mod,
				'author'        => $author,
				'offers'        => array(
					'@type'         => 'Offer',
					'price'         => $price,
					'priceCurrency' => $currency,
					'availability'  => 'https://schema.org/InStock',
					'url'           => $url,
				),
			);
		} elseif ( 'post' === $post->post_type ) {
			$graph[] = array(
				'@type'            => 'Article',
				'@id'              => $url . '#article',
				'headline'         => $title,
				'mainEntityOfPage' => $url,
				'datePublished'    => $date_pub,
				'dateModified'     => $date_mod,
				'author'           => $author,
				'publisher'        => array(
					'@type' => 'Organization',
					'name'  => get_bloginfo( 'name' ),
					'url'   => home_url( '/' ),
				),
			);
		}

		$graph[] = array(
			'@type'         => 'WebPage',
			'@id'           => $url . '#webpage',
			'url'           => $url,
			'name'          => $title,
			'datePublished' => $date_pub,
			'dateModified'  => $date_mod,
			'author'        => $author,
		);

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => __( 'Home', 'nexura-ai-search-optimizer' ),
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $title,
					'item'     => $url,
				),
			),
		);

		return array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);
	}
}
