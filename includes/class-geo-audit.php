<?php
/**
 * Generative Engine Optimization (GEO) Audit Engine
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Evaluates web content using Princeton-researched Generative Engine Optimization criteria.
 */
class GEO_Audit {

	/**
	 * Run an on-page GEO audit on a given Post ID or raw HTML/text.
	 *
	 * @param int $post_id Post ID.
	 * @return array Audit result data structure.
	 */
	public function audit_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array(
				'success' => false,
				'message' => __( 'Post not found.', 'nexura-ai-search-optimizer' ),
			);
		}

		// Transactional utility pages (Cart, Checkout, Account, etc.) are excluded from AI search scoring.
		if ( \NexuraAISearch\LLMS_Generator::is_excluded_utility_page( $post ) ) {
			delete_post_meta( $post_id, '_naso_geo_score' );
			delete_post_meta( $post_id, '_naso_geo_rating' );
			delete_post_meta( $post_id, '_naso_geo_badge' );
			delete_post_meta( $post_id, '_naso_geo_recs' );
			delete_post_meta( $post_id, '_naso_geo_last_audit' );

			return array(
				'success'         => false,
				'is_utility'      => true,
				'id'              => $post_id,
				'title'           => get_the_title( $post ),
				'score'           => null,
				'rating'          => __( 'System Page (Excluded)', 'nexura-ai-search-optimizer' ),
				'badge_class'     => 'naso-badge-utility',
				'message'         => __( 'This is a system/e-commerce utility page (Cart, Checkout, Account, etc.) and is intentionally excluded from GEO scoring and AI search indexing.', 'nexura-ai-search-optimizer' ),
				'recommendations' => array(),
				'breakdown'       => array(),
			);
		}

		$title   = get_the_title( $post );
		$content = $post->post_content;
		$url     = get_permalink( $post );

		// Ensure global $post is populated during AJAX audits so shortcodes/blocks resolve get_the_ID()
		$prev_post       = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		// Render content with standard filters
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Standard WordPress core filter.
		$rendered_content = apply_filters( 'the_content', $content );

		$GLOBALS['post'] = $prev_post;
		if ( $prev_post instanceof \WP_Post ) {
			setup_postdata( $prev_post );
		} else {
			wp_reset_postdata();
		}

		// Prepend AI Key Takeaways summary if configured on the post so BLUF scoring accounts for it
		$ai_summary = get_post_meta( $post_id, '_naso_ai_summary', true );
		if ( ! empty( $ai_summary ) && strpos( $rendered_content, 'naso-ai-summary-box' ) === false ) {
			$rendered_content = '<p class="naso-ai-summary-box">' . esc_html( $ai_summary ) . "</p>\n" . $rendered_content;
		}

		// If page markup lacks JSON-LD, incorporate post schema diagnostics
		if ( strpos( $rendered_content, 'application/ld+json' ) === false ) {
			require_once NEXURA_AI_SEARCH_DIR . 'includes/class-schema-diagnostics.php';
			$schema = Schema_Diagnostics::generate_post_schema( $post_id );
			if ( ! empty( $schema ) ) {
				$rendered_content .= "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
			}
		}

		return $this->evaluate_content( $title, $rendered_content, $url, $post_id );
	}

	/**
	 * Evaluate content string and generate score.
	 *
	 * @param string   $title Content title.
	 * @param string   $html Rendered HTML content.
	 * @param string   $url Canonical URL.
	 * @param int|null $post_id Optional post ID.
	 * @return array
	 */
	public function evaluate_content( $title, $html, $url = '', $post_id = null ) {
		$plain_text = wp_strip_all_tags( $html );
		$words      = preg_split( '/\s+/', trim( $plain_text ), -1, PREG_SPLIT_NO_EMPTY );
		$word_count = count( $words );

		$breakdown = array();
		$recommendations = array();

		// 1. BLUF Analysis (Bottom Line Up Front) - Max 20 pts
		$bluf_score = 0;
		$first_150_words = implode( ' ', array_slice( $words, 0, 150 ) );
		$answer_signals  = array( 'is a', 'are', 'means', 'defined as', 'in summary', 'the best', 'because', 'how to', 'key takeaway' );
		$has_bluf_signal = false;
		foreach ( $answer_signals as $signal ) {
			if ( stripos( $first_150_words, $signal ) !== false ) {
				$has_bluf_signal = true;
				break;
			}
		}

		if ( $word_count >= 150 && $has_bluf_signal ) {
			$bluf_score = 20;
			$bluf_status = __( 'Strong direct answer identified in the opening 150 words.', 'nexura-ai-search-optimizer' );
		} elseif ( $has_bluf_signal ) {
			$bluf_score = 14;
			$bluf_status = __( 'Direct answer signal detected, but opening section is brief.', 'nexura-ai-search-optimizer' );
		} else {
			$bluf_score = 6;
			$bluf_status = __( 'Opening 150 words lacks a direct summary or answer for AI Overviews.', 'nexura-ai-search-optimizer' );
			$recommendations[] = __( 'Add a BLUF (Bottom Line Up Front) direct answer or summary paragraph in the first 2-3 sentences.', 'nexura-ai-search-optimizer' );
		}

		$breakdown['bluf'] = array(
			'title'  => __( 'BLUF (Bottom Line Up Front)', 'nexura-ai-search-optimizer' ),
			'score'  => $bluf_score,
			'max'    => 20,
			'status' => $bluf_status,
		);

		// 2. Statistical & Numerical Density - Max 20 pts
		$stats_score = 0;
		// Count numbers, percentages, and metrics
		preg_match_all( '/\b\d+([.,]\d+)?%|\$\d+|\b\d{4}\b|\b\d+\s*(percent|users|million|billion|times|x|hours|days|years)\b/i', $plain_text, $stat_matches );
		$stat_count = isset( $stat_matches[0] ) ? count( $stat_matches[0] ) : 0;

		if ( $stat_count >= 5 ) {
			$stats_score = 20;
			/* translators: %d: number of statistics found */
			$stats_status = sprintf( __( 'Excellent data density: %d statistics/data points found.', 'nexura-ai-search-optimizer' ), $stat_count );
		} elseif ( $stat_count >= 2 ) {
			$stats_score = 13;
			/* translators: %d: number of data points found */
			$stats_status = sprintf( __( 'Moderate data density: %d data points found.', 'nexura-ai-search-optimizer' ), $stat_count );
			$recommendations[] = __( 'Incorporate more specific statistics, percentages, or data points to increase quotation likelihood.', 'nexura-ai-search-optimizer' );
		} else {
			$stats_score = 5;
			$stats_status = __( 'Low statistical density. AI engines strongly favor quantifiable facts.', 'nexura-ai-search-optimizer' );
			$recommendations[] = __( 'Add concrete numbers, percentages, benchmark metrics, or research statistics.', 'nexura-ai-search-optimizer' );
		}

		$breakdown['statistics'] = array(
			'title'  => __( 'Statistical & Fact Density', 'nexura-ai-search-optimizer' ),
			'score'  => $stats_score,
			'max'    => 20,
			'status' => $stats_status,
		);

		// 3. Structural Hierarchy & Scannability - Max 20 pts
		$structure_score = 0;
		preg_match_all( '/<h[2-3][^>]*>/i', $html, $h_matches );
		preg_match_all( '/<ul|<ol/i', $html, $list_matches );
		preg_match_all( '/<table/i', $html, $table_matches );

		$h_count     = isset( $h_matches[0] ) ? count( $h_matches[0] ) : 0;
		$list_count  = isset( $list_matches[0] ) ? count( $list_matches[0] ) : 0;
		$table_count = isset( $table_matches[0] ) ? count( $table_matches[0] ) : 0;

		if ( $h_count >= 3 && ( $list_count >= 1 || $table_count >= 1 ) ) {
			$structure_score = 20;
			$structure_status = __( 'Optimal heading hierarchy with bulleted lists/tables for quick parsing.', 'nexura-ai-search-optimizer' );
		} elseif ( $h_count >= 2 ) {
			$structure_score = 14;
			$structure_status = __( 'Good subheadings, but could benefit from bulleted lists or comparison tables.', 'nexura-ai-search-optimizer' );
			$recommendations[] = __( 'Break down long paragraphs into bulleted lists or comparison tables for AI crawler indexing.', 'nexura-ai-search-optimizer' );
		} else {
			$structure_score = 6;
			$structure_status = __( 'Monolithic text detected. Lacks sufficient H2/H3 subheadings.', 'nexura-ai-search-optimizer' );
			$recommendations[] = __( 'Structure content with descriptive H2/H3 subheadings matching common user questions.', 'nexura-ai-search-optimizer' );
		}

		$breakdown['structure'] = array(
			'title'  => __( 'Structural Hierarchy & Lists', 'nexura-ai-search-optimizer' ),
			'score'  => $structure_score,
			'max'    => 20,
			'status' => $structure_status,
		);

		// 4. Citation & Authority Sources - Max 15 pts
		$citation_score = 0;
		preg_match_all( '#<a\s+[^>]*href=[\'"](https?://[^\'"]+)[\'"][^>]*>#i', $html, $link_matches );
		$ext_links = 0;
		$home_domain = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( ! empty( $link_matches[1] ) ) {
			foreach ( $link_matches[1] as $lnk ) {
				$link_domain = wp_parse_url( $lnk, PHP_URL_HOST );
				if ( $link_domain && $link_domain !== $home_domain ) {
					$ext_links++;
				}
			}
		}

		if ( $ext_links >= 3 ) {
			$citation_score = 15;
			/* translators: %d: number of external references found */
			$citation_status = sprintf( __( 'Strong citation authority: %d external references found.', 'nexura-ai-search-optimizer' ), $ext_links );
		} elseif ( $ext_links >= 1 ) {
			$citation_score = 10;
			/* translators: %d: number of external links found */
			$citation_status = sprintf( __( 'Moderate citation references: %d external link found.', 'nexura-ai-search-optimizer' ), $ext_links );
		} else {
			$citation_score = 4;
			$citation_status = __( 'No external reference citations found.', 'nexura-ai-search-optimizer' );
			$recommendations[] = __( 'Cite authoritative sources, industry reports, or documentation to strengthen E-E-A-T signals.', 'nexura-ai-search-optimizer' );
		}

		$breakdown['citation'] = array(
			'title'  => __( 'Citations & Sources', 'nexura-ai-search-optimizer' ),
			'score'  => $citation_score,
			'max'    => 15,
			'status' => $citation_status,
		);

		// 5. Schema Diagnostics & Machine-Readability - Max 15 pts
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-schema-diagnostics.php';
		$diagnostics = new Schema_Diagnostics();
		$schema_res  = $diagnostics->diagnose_html( $html );
		$schema_score = $schema_res['ai_score_impact'];

		/* translators: %s: comma separated schema types found */
		$schema_status_text = ! empty( $schema_res['schemas_found'] ) ? sprintf( __( 'Found schema types: %s', 'nexura-ai-search-optimizer' ), implode( ', ', $schema_res['schemas_found'] ) ) : __( 'No schema detected in page markup.', 'nexura-ai-search-optimizer' );

		$breakdown['schema'] = array(
			'title'  => __( 'Schema & JSON-LD Quality', 'nexura-ai-search-optimizer' ),
			'score'  => $schema_score,
			'max'    => 15,
			'status' => $schema_status_text,
		);

		if ( ! empty( $schema_res['recommendations'] ) ) {
			foreach ( $schema_res['recommendations'] as $rec ) {
				$recommendations[] = $rec;
			}
		}

		// 6. Brevity & Fluff Index - Max 10 pts
		$fluff_score = 10;
		$fluff_phrases = array(
			'in today\'s fast-paced world',
			'without further ado',
			'delve into',
			'it is worth noting',
			'as a matter of fact',
			'at the end of the day',
			'needless to say',
		);
		$fluff_found = 0;
		foreach ( $fluff_phrases as $phrase ) {
			if ( stripos( $plain_text, $phrase ) !== false ) {
				$fluff_found++;
			}
		}

		if ( $fluff_found > 2 ) {
			$fluff_score = 4;
			/* translators: %d: generic filler phrases count */
			$fluff_status = sprintf( __( 'Detected %d generic filler phrases. AI crawlers prioritize dense, concise prose.', 'nexura-ai-search-optimizer' ), $fluff_found );
			$recommendations[] = __( 'Remove generic conversational filler (e.g., "in today\'s world", "delve into") to increase information density.', 'nexura-ai-search-optimizer' );
		} elseif ( $fluff_found > 0 ) {
			$fluff_score = 7;
			$fluff_status = __( 'Minor filler phrases detected.', 'nexura-ai-search-optimizer' );
		} else {
			$fluff_score = 10;
			$fluff_status = __( 'Clean, high information-to-noise ratio.', 'nexura-ai-search-optimizer' );
		}

		$breakdown['fluff'] = array(
			'title'  => __( 'Information Density (Fluff Index)', 'nexura-ai-search-optimizer' ),
			'score'  => $fluff_score,
			'max'    => 10,
			'status' => $fluff_status,
		);

		// Calculate Total Score
		$total_score = $bluf_score + $stats_score + $structure_score + $citation_score + $schema_score + $fluff_score;
		$total_score = max( 0, min( 100, $total_score ) );

		if ( $total_score >= 80 ) {
			$rating      = __( 'Optimal for AI Engine Citations', 'nexura-ai-search-optimizer' );
			$badge_class = 'naso-badge-success';
		} elseif ( $total_score >= 50 ) {
			$rating      = __( 'Needs GEO Optimization', 'nexura-ai-search-optimizer' );
			$badge_class = 'naso-badge-warning';
		} else {
			$rating      = __( 'High Risk of AI Invisibility', 'nexura-ai-search-optimizer' );
			$badge_class = 'naso-badge-danger';
		}

		if ( $post_id ) {
			update_post_meta( $post_id, '_naso_geo_score', $total_score );
			update_post_meta( $post_id, '_naso_geo_rating', $rating );
			update_post_meta( $post_id, '_naso_geo_badge', $badge_class );
			update_post_meta( $post_id, '_naso_geo_recs', array_values( array_unique( $recommendations ) ) );
			update_post_meta( $post_id, '_naso_geo_last_audit', time() );
		}

		return array(
			'success'         => true,
			'id'              => $post_id,
			'title'           => $title,
			'url'             => $url,
			'word_count'      => $word_count,
			'score'           => $total_score,
			'rating'          => $rating,
			'badge_class'     => $badge_class,
			'breakdown'       => $breakdown,
			'recommendations' => array_values( array_unique( $recommendations ) ),
		);
	}
}
