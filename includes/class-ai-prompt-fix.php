<?php
/**
 * 1-Click AI Fix Prompt Generator
 *
 * Formulates Princeton University GEO-compliant rewrite prompts for
 * ChatGPT, Claude, and Perplexity based on detected on-page audit weaknesses.
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AI Fix Prompt engineering and guidance.
 */
class AI_Prompt_Fix {

	/**
	 * Alias for generate_prompt_data to ensure backward compatibility.
	 *
	 * @param int $post_id Post or Product ID.
	 * @return array
	 */
	public static function generate_prompt( $post_id ) {
		return self::generate_prompt_data( $post_id );
	}

	/**
	 * Build comprehensive AI optimization prompt for a given post.
	 *
	 * @param int $post_id Post or Product ID.
	 * @return array
	 */
	public static function generate_prompt_data( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array(
				'success' => false,
				'message' => __( 'Post not found.', 'nexura-ai-search-optimizer' ),
			);
		}

		$auditor = new GEO_Audit();
		$audit   = $auditor->audit_post( $post_id );

		$title      = html_entity_decode( get_the_title( $post ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$post_type  = get_post_type( $post );
		$content    = html_entity_decode( wp_strip_all_tags( $post->post_content ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$score      = isset( $audit['score'] ) ? absint( $audit['score'] ) : 0;
		$rating     = isset( $audit['rating'] ) ? sanitize_text_field( $audit['rating'] ) : '';
		$recs       = isset( $audit['recommendations'] ) ? (array) $audit['recommendations'] : array();
		$breakdown  = isset( $audit['breakdown'] ) ? (array) $audit['breakdown'] : array();

		// Truncate content preview for prompt if extremely long (keep first 3000 words max)
		$words = preg_split( '/\s+/', trim( $content ) );
		if ( count( $words ) > 3000 ) {
			$content = implode( ' ', array_slice( $words, 0, 3000 ) ) . "\n\n[...content truncated for prompt length...]";
		}

		$is_product = ( 'product' === $post_type );

		$prompt  = "Act as an elite Generative Engine Optimization (GEO) and Content Engineering specialist.\n";
		$prompt .= "Your mission is to rewrite and optimize the content below so that it achieves top citations and AI Overviews in ChatGPT Search, Perplexity AI, Claude, and Google AI Overviews.\n\n";

		$prompt .= "=== CONTENT OVERVIEW ===\n";
		$prompt .= "Title: " . $title . "\n";
		$prompt .= "Content Type: " . ( $is_product ? "E-Commerce Product Description" : "Informational Article / Guide" ) . "\n";
		$prompt .= "Current GEO Score: " . $score . " / 100 (" . $rating . ")\n\n";

		$prompt .= "=== IDENTIFIED GEO WEAKNESSES TO ELIMINATE ===\n";
		if ( ! empty( $recs ) ) {
			foreach ( $recs as $rec ) {
				$prompt .= "- " . $rec . "\n";
			}
		} else {
			$prompt .= "- None! Content achieved a perfect 100/100 GEO citation score. Focus on maintaining freshness and adding future updates.\n";
		}
		$prompt .= "\n";

		$prompt .= "=== EMPIRICAL GEO INSTRUCTIONS (Princeton & Georgia Tech Research) ===\n";
		$prompt .= "1. BOTTOM LINE UP FRONT (BLUF):\n";
		$prompt .= "   - Begin with a direct, comprehensive 2-3 sentence answer or summary in the opening paragraph.\n";
		$prompt .= "   - Never start with conversational filler (e.g. 'In today's fast-paced world...'). AI crawlers demand immediate answers.\n\n";

		$prompt .= "2. FACTUAL & STATISTICAL DENSITY:\n";
		$prompt .= "   - Weave in verifiable data points, percentages, benchmark numbers, or quantitative specifics where appropriate.\n\n";

		$prompt .= "3. HIERARCHICAL QUESTION-BASED HEADINGS:\n";
		$prompt .= "   - Organize sections using semantic H2 and H3 headings matching common user search queries.\n";
		$prompt .= "   - Use bullet points and numbered steps for processes.\n\n";

		$prompt .= "4. E-E-A-T & CITATIONS:\n";
		$prompt .= "   - Cite authoritative primary sources, industry benchmarks, or expert quotes to build citation credibility.\n\n";

		$prompt .= "5. FLUFF INDEX ELIMINATION:\n";
		$prompt .= "   - Ruthlessly remove repetitive marketing fluff, buzzwords, and redundant phrases. Keep information density high.\n\n";

		if ( $is_product ) {
			$prompt .= "6. E-COMMERCE & BUYER INTENT OPTIMIZATION (AI Shopping Agent Ready):\n";
			$prompt .= "   - Include a concise bulleted specifications list (Dimensions, Materials, Compatibility, Warranty).\n";
			$prompt .= "   - Add a 3-question 'Frequently Asked Buyer Questions' section addressing common purchase objections.\n";
			$prompt .= "   - Clearly state key differentiating benefits over alternatives.\n\n";
		}

		$prompt .= "=== ORIGINAL CONTENT TO REWRITE ===\n";
		$prompt .= "--------------------------------------------------\n";
		$prompt .= $content . "\n";
		$prompt .= "--------------------------------------------------\n\n";

		$prompt .= "=== OUTPUT REQUIREMENTS ===\n";
		$prompt .= "Provide the complete, revised, publication-ready content in clean Markdown format with proper headings, lists, and direct answers ready to publish directly on the site.\n";

		return array(
			'success'         => true,
			'post_id'         => $post_id,
			'title'           => $title,
			'score'           => $score,
			'rating'          => $rating,
			'badge_class'     => isset( $audit['badge_class'] ) ? $audit['badge_class'] : 'naso-badge-warning',
			'issues'          => $recs,
			'recommendations' => $recs,
			'breakdown'       => $breakdown,
			'prompt'           => $prompt,
			'is_perfect_score' => ( $score >= 100 && empty( $recs ) ),
		);
	}
}
