<?php
/**
 * HTML to Markdown Output Converter
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts WordPress posts/pages to clean semantic Markdown for LLMs and AI scrapers.
 */
class Markdown_Output {

	/**
	 * Register hooks for auto-discovery link tags in HTML head.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_head', array( $this, 'output_head_links' ), 5 );
	}

	/**
	 * Output auto-discovery link headers in wp_head.
	 *
	 * @return void
	 */
	public function output_head_links() {
		// Output llms-txt discovery link on all public pages
		if ( Settings::get( 'llms_enabled', 1 ) ) {
			echo '<link rel="llms-txt" href="' . esc_url( home_url( '/llms.txt' ) ) . '" title="LLMs.txt" />' . "\n";
		}

		// Output alternate markdown link on singular posts/pages
		if ( is_singular() && Settings::get( 'markdown_output_enabled', 1 ) && Settings::get( 'markdown_head_link', 1 ) ) {
			$post_id  = get_queried_object_id();
			$post_url = get_permalink( $post_id );
			if ( $post_url ) {
				$markdown_url = add_query_arg( 'format', 'markdown', $post_url );
				echo '<link rel="alternate" type="text/markdown" href="' . esc_url( $markdown_url ) . '" title="' . esc_attr__( 'Markdown Format for AI Agents', 'nexura-ai-search-optimizer' ) . '" />' . "\n";
			}
		}

		// Output GEO Schema JSON-LD on singular posts/pages if no third-party SEO plugin is handling it
		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			if ( $post_id && ( ! Schema_Diagnostics::has_seo_plugin() || get_post_meta( $post_id, '_naso_schema_json_ld', true ) ) ) {
				$schema = Schema_Diagnostics::generate_post_schema( $post_id );
				if ( ! empty( $schema ) ) {
					echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
				}
			}
		}
	}

	/**
	 * Render single post or page directly as clean Markdown.
	 *
	 * @param int $post_id Post ID to render.
	 * @return void
	 */
	public function render_singular( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			wp_die( esc_html__( 'Content not found or not published.', 'nexura-ai-search-optimizer' ), 404 );
		}

		// Exclude WooCommerce utility pages from AI Markdown endpoint
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-llms-generator.php';
		if ( LLMS_Generator::is_excluded_utility_page( $post ) ) {
			wp_die( esc_html__( 'This utility page is not indexed for AI consumption.', 'nexura-ai-search-optimizer' ), 403 );
		}

		$title       = LLMS_Generator::clean_plain_text( get_the_title( $post ) );
		$author      = LLMS_Generator::clean_plain_text( get_the_author_meta( 'display_name', $post->post_author ) );
		$date        = get_the_date( 'Y-m-d', $post );
		$modified    = get_the_modified_date( 'Y-m-d', $post );
		$url         = get_permalink( $post );
		$raw_desc    = get_the_excerpt( $post );
		$description = LLMS_Generator::clean_plain_text( wp_strip_all_tags( $raw_desc ) );

		$output  = "---\n";
		$output .= 'title: ' . wp_json_encode( $title ) . "\n";
		$output .= 'url: ' . esc_url_raw( $url ) . "\n";
		$output .= 'author: ' . wp_json_encode( $author ) . "\n";
		$output .= 'date: ' . sanitize_text_field( $date ) . "\n";
		$output .= 'modified: ' . sanitize_text_field( $modified ) . "\n";

		// Add categories if post
		if ( 'post' === $post->post_type ) {
			$categories = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
			if ( ! empty( $categories ) && is_array( $categories ) ) {
				$output .= 'categories: [' . implode( ', ', array_map( function( $c ) { return wp_json_encode( LLMS_Generator::clean_plain_text( $c ) ); }, $categories ) ) . "]\n";
			}
		}

		$output .= "---\n\n";

		$output .= '# ' . $title . "\n\n";

		if ( ! empty( $description ) ) {
			$output .= '> ' . trim( $description ) . "\n\n";
		}

		// Filter the content using WordPress default filters (to expand shortcodes & blocks)
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Standard WordPress core filter.
		$raw_html = apply_filters( 'the_content', $post->post_content );

		// Convert HTML to clean Markdown
		$output .= $this->convert_html_to_markdown( $raw_html );

		// Send Headers (noindex, follow prevents Google duplicate content penalty while allowing AI bots)
		header( 'Content-Type: text/markdown; charset=utf-8' );
		header( 'X-Robots-Tag: noindex, follow' );
		header( 'Link: <' . esc_url_raw( $url ) . '>; rel="canonical"' );
		header( 'Cache-Control: public, max-age=86400' );

		echo wp_kses_post( $output );
		exit;
	}

	/**
	 * Convert standard HTML markup to clean, LLM-friendly Markdown.
	 *
	 * @param string $html HTML string.
	 * @return string Markdown output.
	 */
	public function convert_html_to_markdown( $html ) {
		if ( empty( $html ) ) {
			return '';
		}

		// 1. Remove dangerous or non-content HTML elements
		$html = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $html );
		$html = preg_replace( '#<style(.*?)>(.*?)</style>#is', '', $html );
		$html = preg_replace( '#<nav(.*?)>(.*?)</nav>#is', '', $html );
		$html = preg_replace( '#<footer(.*?)>(.*?)</footer>#is', '', $html );
		$html = preg_replace( '#<header(.*?)>(.*?)</header>#is', '', $html );
		$html = preg_replace( '#<aside(.*?)>(.*?)</aside>#is', '', $html );
		$html = preg_replace( '#<iframe(.*?)>(.*?)</iframe>#is', '', $html );
		$html = preg_replace( '#<!--(.*?)-->#is', '', $html );

		// 2. Headings (h1 - h6)
		for ( $i = 6; $i >= 1; $i-- ) {
			$prefix = str_repeat( '#', $i ) . ' ';
			$html   = preg_replace_callback(
				'#<h' . $i . '[^>]*>(.*?)</h' . $i . '>#is',
				function( $matches ) use ( $prefix ) {
					return "\n\n" . $prefix . trim( wp_strip_all_tags( $matches[1] ) ) . "\n\n";
				},
				$html
			);
		}

		// 3. Code blocks (<pre><code> ... </code></pre>)
		$html = preg_replace_callback(
			'#<pre[^>]*><code[^>]*>(.*?)</code></pre>#is',
			function( $matches ) {
				$code = html_entity_decode( $matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				return "\n\n```\n" . trim( $code ) . "\n```\n\n";
			},
			$html
		);

		// 4. Inline code (<code> ... </code>)
		$html = preg_replace_callback(
			'#<code[^>]*>(.*?)</code>#is',
			function( $matches ) {
				return ' `' . trim( wp_strip_all_tags( $matches[1] ) ) . '` ';
			},
			$html
		);

		// 5. Blockquotes
		$html = preg_replace_callback(
			'#<blockquote[^>]*>(.*?)</blockquote>#is',
			function( $matches ) {
				$clean = trim( wp_strip_all_tags( $matches[1] ) );
				$lines = explode( "\n", $clean );
				$quoted = array_map( function( $l ) { return '> ' . trim( $l ); }, $lines );
				return "\n\n" . implode( "\n", $quoted ) . "\n\n";
			},
			$html
		);

		// 6. Bold and Italic
		$html = preg_replace( '#<(strong|b)[^>]*>(.*?)</\1>#is', '**$2**', $html );
		$html = preg_replace( '#<(em|i)[^>]*>(.*?)</\1>#is', '*$2*', $html );

		// 7. Links (<a href="...">...</a>)
		$html = preg_replace_callback(
			'#<a\s+[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)</a>#is',
			function( $matches ) {
				$url  = $matches[1];
				$text = trim( wp_strip_all_tags( $matches[2] ) );
				if ( empty( $text ) ) {
					$text = $url;
				}
				return '[' . $text . '](' . $url . ')';
			},
			$html
		);

		// 8. Images (<img src="..." alt="...">)
		$html = preg_replace_callback(
			'#<img\s+[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>#is',
			function( $matches ) {
				$tag = $matches[0];
				$src = $matches[1];
				$alt = '';
				if ( preg_match( '#alt=[\'"]([^\'"]*)[\'"]#i', $tag, $alt_match ) ) {
					$alt = $alt_match[1];
				}
				return '![' . $alt . '](' . $src . ')';
			},
			$html
		);

		// 9. Unordered Lists (<ul><li>...</li></ul>)
		$html = preg_replace_callback(
			'#<ul[^>]*>(.*?)</ul>#is',
			function( $matches ) {
				$list_content = $matches[1];
				preg_match_all( '#<li[^>]*>(.*?)</li>#is', $list_content, $li_matches );
				$items = array();
				if ( ! empty( $li_matches[1] ) ) {
					foreach ( $li_matches[1] as $item ) {
						$items[] = '- ' . trim( wp_strip_all_tags( $item ) );
					}
				}
				return "\n\n" . implode( "\n", $items ) . "\n\n";
			},
			$html
		);

		// 10. Ordered Lists (<ol><li>...</li></ol>)
		$html = preg_replace_callback(
			'#<ol[^>]*>(.*?)</ol>#is',
			function( $matches ) {
				$list_content = $matches[1];
				preg_match_all( '#<li[^>]*>(.*?)</li>#is', $list_content, $li_matches );
				$items = array();
				if ( ! empty( $li_matches[1] ) ) {
					$counter = 1;
					foreach ( $li_matches[1] as $item ) {
						$items[] = $counter . '. ' . trim( wp_strip_all_tags( $item ) );
						$counter++;
					}
				}
				return "\n\n" . implode( "\n", $items ) . "\n\n";
			},
			$html
		);

		// 11. Tables (convert simple tables to Markdown)
		$html = preg_replace_callback(
			'#<table[^>]*>(.*?)</table>#is',
			function( $matches ) {
				$table_html = $matches[1];
				// Find headers
				preg_match_all( '#<th[^>]*>(.*?)</th>#is', $table_html, $headers );
				// Find rows
				preg_match_all( '#<tr[^>]*>(.*?)</tr>#is', $table_html, $rows );

				if ( empty( $rows[0] ) ) {
					return '';
				}

				$markdown_table = "\n\n";
				$header_cols    = array();

				if ( ! empty( $headers[1] ) ) {
					foreach ( $headers[1] as $h ) {
						$header_cols[] = trim( wp_strip_all_tags( $h ) );
					}
					$markdown_table .= '| ' . implode( ' | ', $header_cols ) . " |\n";
					$markdown_table .= '| ' . implode( ' | ', array_fill( 0, count( $header_cols ), '---' ) ) . " |\n";
				}

				foreach ( $rows[1] as $row ) {
					preg_match_all( '#<td[^>]*>(.*?)</td>#is', $row, $tds );
					if ( ! empty( $tds[1] ) ) {
						$row_cols = array();
						foreach ( $tds[1] as $td ) {
							$row_cols[] = trim( wp_strip_all_tags( $td ) );
						}
						if ( empty( $header_cols ) ) {
							$header_cols = array_fill( 0, count( $row_cols ), 'Col' );
							$markdown_table .= '| ' . implode( ' | ', $header_cols ) . " |\n";
							$markdown_table .= '| ' . implode( ' | ', array_fill( 0, count( $row_cols ), '---' ) ) . " |\n";
						}
						$markdown_table .= '| ' . implode( ' | ', $row_cols ) . " |\n";
					}
				}
				return $markdown_table . "\n\n";
			},
			$html
		);

		// 12. Paragraphs & Line breaks
		$html = preg_replace( '#<br\s*/?>#i', "\n", $html );
		$html = preg_replace( '#<p[^>]*>(.*?)</p>#is', "\n\n$1\n\n", $html );

		// 13. Strip any remaining HTML tags
		$clean = wp_strip_all_tags( $html );

		// 14. Decode HTML entities and clean ellipses for AI models
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-llms-generator.php';
		$clean = LLMS_Generator::clean_plain_text( $clean );

		// 15. Normalize excessive newlines (max 2 consecutive newlines)
		$clean = preg_replace( "/\n{3,}/", "\n\n", $clean );

		return trim( $clean );
	}
}
