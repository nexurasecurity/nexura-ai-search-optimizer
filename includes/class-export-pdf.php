<?php
/**
 * Professional White-Label PDF / Printable Audit Report Generator
 *
 * Generates beautiful, pixel-perfect, printable GEO audit reports formatted
 * for A4 paper and PDF export via native browser print engine.
 * Fully compatible with low-end shared hosting (zero heavy external binary dependencies).
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles PDF and print export for single and bulk GEO audits.
 */
class Export_PDF {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_post_naso_export_pdf', array( $this, 'generate_report' ) );
	}

	/**
	 * Dispatch report generation request.
	 *
	 * @return void
	 */
	public function generate_report() {
		check_admin_referer( 'naso_export_pdf', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'single';

		if ( 'bulk' === $type ) {
			$this->render_bulk_report();
		} else {
			$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
			if ( ! $post_id ) {
				wp_die( esc_html__( 'Missing post ID.', 'nexura-ai-search-optimizer' ) );
			}
			$this->render_single_report( $post_id );
		}
		exit;
	}

	/**
	 * Render single post GEO audit report.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function render_single_report( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_die( esc_html__( 'Post not found.', 'nexura-ai-search-optimizer' ) );
		}

		$auditor = new GEO_Audit();
		$audit   = $auditor->audit_post( $post_id );

		$site_title = get_bloginfo( 'name' );
		$site_url   = home_url();
		$post_title = get_the_title( $post );
		$permalink  = get_permalink( $post );
		$score      = isset( $audit['score'] ) ? absint( $audit['score'] ) : 0;
		$rating     = isset( $audit['rating'] ) ? sanitize_text_field( $audit['rating'] ) : '';
		$word_count = isset( $audit['word_count'] ) ? absint( $audit['word_count'] ) : 0;
		$breakdown  = isset( $audit['breakdown'] ) && is_array( $audit['breakdown'] ) ? $audit['breakdown'] : array();
		$recs       = isset( $audit['recommendations'] ) && is_array( $audit['recommendations'] ) ? $audit['recommendations'] : array();
		$date       = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		$score_color = '#d63638';
		if ( $score >= 80 ) {
			$score_color = '#00a32a';
		} elseif ( $score >= 60 ) {
			$score_color = '#dba617';
		}

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>
				<?php
				/* translators: %s: post title */
				echo esc_html( sprintf( __( 'GEO Audit Report - %s', 'nexura-ai-search-optimizer' ), $post_title ) );
				?>
			</title>
			<style>
				@page {
					size: A4;
					margin: 12mm 15mm 15mm 15mm;
				}
				* {
					box-sizing: border-box;
					-webkit-print-color-adjust: exact !important;
					print-color-adjust: exact !important;
				}
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					color: #1d2327;
					background: #f0f0f1;
					margin: 0;
					padding: 0;
					font-size: 13px;
					line-height: 1.5;
				}
				.no-print-bar {
					background: #1d2327;
					color: #fff;
					padding: 12px 25px;
					display: flex;
					align-items: center;
					justify-content: space-between;
					position: sticky;
					top: 0;
					z-index: 9999;
					box-shadow: 0 2px 8px rgba(0,0,0,0.15);
				}
				.no-print-bar .btn-print {
					background: #2271b1;
					color: #fff;
					border: none;
					padding: 8px 18px;
					font-size: 14px;
					font-weight: 600;
					border-radius: 4px;
					cursor: pointer;
					display: inline-flex;
					align-items: center;
					gap: 6px;
				}
				.no-print-bar .btn-print:hover {
					background: #135e96;
				}
				.no-print-bar .btn-close {
					color: #a7aaad;
					background: none;
					border: none;
					font-size: 13px;
					cursor: pointer;
					text-decoration: underline;
				}
				.report-container {
					max-width: 820px;
					margin: 30px auto;
					background: #fff;
					padding: 40px;
					border-radius: 6px;
					box-shadow: 0 4px 15px rgba(0,0,0,0.06);
				}
				.report-header {
					display: flex;
					justify-content: space-between;
					align-items: flex-start;
					padding-bottom: 20px;
					border-bottom: 2px solid #2271b1;
					margin-bottom: 25px;
				}
				.report-brand h1 {
					font-size: 20px;
					margin: 0 0 4px 0;
					color: #2271b1;
					letter-spacing: -0.3px;
				}
				.report-brand p {
					margin: 0;
					font-size: 12px;
					color: #646970;
				}
				.report-meta {
					text-align: right;
					font-size: 12px;
					color: #50575e;
				}
				.meta-badge {
					display: inline-block;
					background: #e7f5ea;
					color: #007a20;
					padding: 3px 10px;
					border-radius: 12px;
					font-weight: 600;
					font-size: 11px;
					margin-bottom: 5px;
				}
				.target-box {
					background: #f6f7f7;
					border: 1px solid #dcdcde;
					border-radius: 6px;
					padding: 16px 20px;
					margin-bottom: 25px;
				}
				.target-box h2 {
					font-size: 17px;
					margin: 0 0 6px 0;
					color: #1d2327;
				}
				.target-box .url {
					font-size: 12px;
					color: #2271b1;
					word-break: break-all;
				}
				.score-card {
					display: flex;
					align-items: center;
					gap: 30px;
					background: #fff;
					border: 2px solid #c3c4c7;
					border-radius: 8px;
					padding: 22px 25px;
					margin-bottom: 25px;
				}
				.score-meter {
					text-align: center;
					min-width: 130px;
				}
				.score-number {
					font-size: 56px;
					font-weight: 800;
					line-height: 1;
					color: <?php echo esc_attr( $score_color ); ?>;
				}
				.score-label {
					font-size: 12px;
					text-transform: uppercase;
					color: #646970;
					letter-spacing: 0.5px;
					margin-top: 5px;
					font-weight: 600;
				}
				.score-info h3 {
					margin: 0 0 6px 0;
					font-size: 18px;
					color: #1d2327;
				}
				.score-info p {
					margin: 0;
					color: #50575e;
					font-size: 13px;
				}
				.section-title {
					font-size: 15px;
					font-weight: 700;
					color: #1d2327;
					border-left: 4px solid #2271b1;
					padding-left: 10px;
					margin: 25px 0 15px 0;
				}
				table.breakdown-table {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 25px;
					font-size: 13px;
				}
				table.breakdown-table th {
					background: #f6f7f7;
					color: #1d2327;
					text-align: left;
					padding: 10px 14px;
					border-bottom: 1px solid #c3c4c7;
					font-weight: 600;
				}
				table.breakdown-table td {
					padding: 11px 14px;
					border-bottom: 1px solid #f0f0f1;
					vertical-align: middle;
				}
				.status-text {
					font-size: 12px;
					color: #646970;
				}
				.rec-box {
					background: #f0f6fc;
					border: 1px solid #c8d8f0;
					border-radius: 6px;
					padding: 18px 22px;
					margin-bottom: 25px;
				}
				.rec-list {
					margin: 0;
					padding-left: 20px;
				}
				.rec-list li {
					margin-bottom: 8px;
					color: #1d2327;
					font-size: 13px;
				}
				.report-footer {
					margin-top: 35px;
					padding-top: 15px;
					border-top: 1px solid #dcdcde;
					display: flex;
					justify-content: space-between;
					font-size: 11px;
					color: #8c8f94;
				}
				@media print {
					body {
						background: #fff;
					}
					.no-print-bar {
						display: none !important;
					}
					.report-container {
						max-width: 100%;
						margin: 0;
						padding: 0;
						box-shadow: none;
						border: none;
					}
					.breakdown-table tr {
						page-break-inside: avoid;
					}
					.score-card, .target-box, .rec-box {
						page-break-inside: avoid;
					}
				}
			</style>
		</head>
		<body>

		<div class="no-print-bar">
			<div>
				<strong><?php esc_html_e( 'Nexura AI Search Optimizer - White-Label Audit Report', 'nexura-ai-search-optimizer' ); ?></strong>
			</div>
			<div style="display: flex; gap: 12px; align-items: center;">
				<button type="button" class="btn-print" onclick="window.print();">
					🖨️ <?php esc_html_e( 'Print / Save as PDF', 'nexura-ai-search-optimizer' ); ?>
				</button>
				<button type="button" class="btn-close" onclick="window.close();">
					<?php esc_html_e( 'Close', 'nexura-ai-search-optimizer' ); ?>
				</button>
			</div>
		</div>

		<div class="report-container">
			<!-- Header -->
			<div class="report-header">
				<div class="report-brand">
					<h1><?php echo esc_html( $site_title ); ?></h1>
					<p><?php echo esc_html( $site_url ); ?> &bull; <?php esc_html_e( 'Generative Engine Optimization (GEO) Audit', 'nexura-ai-search-optimizer' ); ?></p>
				</div>
				<div class="report-meta">
					<div class="meta-badge"><?php esc_html_e( 'AI Search Certified Audit', 'nexura-ai-search-optimizer' ); ?></div>
					<div><?php echo esc_html( $date ); ?></div>
				</div>
			</div>

			<!-- Target Content Summary -->
			<div class="target-box">
				<h2><?php echo esc_html( $post_title ); ?></h2>
				<div class="url"><?php echo esc_url( $permalink ); ?></div>
				<div style="margin-top: 6px; font-size: 12px; color: #50575e;">
					<?php esc_html_e( 'Content Length:', 'nexura-ai-search-optimizer' ); ?> <strong><?php echo esc_html( number_format_i18n( $word_count ) ); ?> <?php esc_html_e( 'words', 'nexura-ai-search-optimizer' ); ?></strong>
					&bull; <?php esc_html_e( 'Post ID:', 'nexura-ai-search-optimizer' ); ?> <?php echo esc_html( $post_id ); ?>
				</div>
			</div>

			<!-- Main Score Card -->
			<div class="score-card">
				<div class="score-meter">
					<div class="score-number"><?php echo esc_html( $score ); ?></div>
					<div class="score-label"><?php esc_html_e( 'GEO Score (0-100)', 'nexura-ai-search-optimizer' ); ?></div>
				</div>
				<div class="score-info">
					<h3><?php echo esc_html( $rating ); ?></h3>
					<p>
						<?php
						if ( $score >= 80 ) {
							esc_html_e( 'This content demonstrates exemplary generative readiness. Highly favorable for direct citations in ChatGPT Search, Perplexity, Claude, and Google AI Overviews.', 'nexura-ai-search-optimizer' );
						} elseif ( $score >= 60 ) {
							esc_html_e( 'Moderate AI discoverability. The content has good foundational depth, but addressing the structural recommendations below will dramatically boost citation likelihood.', 'nexura-ai-search-optimizer' );
						} else {
							esc_html_e( 'Needs structural optimization. AI crawlers and LLMs may deprioritize this content due to high fluff or missing authoritative citations.', 'nexura-ai-search-optimizer' );
						}
						?>
					</p>
				</div>
			</div>

			<!-- Breakdown Criteria Table -->
			<div class="section-title"><?php esc_html_e( 'Princeton University GEO Citation Criteria Evaluation', 'nexura-ai-search-optimizer' ); ?></div>
			<table class="breakdown-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Evaluation Metric', 'nexura-ai-search-optimizer' ); ?></th>
						<th style="width: 100px; text-align: center;"><?php esc_html_e( 'Score', 'nexura-ai-search-optimizer' ); ?></th>
						<th><?php esc_html_e( 'Assessment & Diagnostics', 'nexura-ai-search-optimizer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $breakdown ) ) : ?>
						<?php foreach ( $breakdown as $item ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $item['title'] ); ?></strong>
								</td>
								<td style="text-align: center; font-weight: 700; color: #2271b1;">
									<?php echo esc_html( $item['score'] ); ?> / <?php echo esc_html( $item['max'] ); ?>
								</td>
								<td class="status-text">
									<?php echo esc_html( $item['status'] ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<!-- Recommendations Checklist -->
			<div class="section-title"><?php esc_html_e( 'Actionable Content Optimization Checklist', 'nexura-ai-search-optimizer' ); ?></div>
			<div class="rec-box">
				<?php if ( ! empty( $recs ) ) : ?>
					<ul class="rec-list">
						<?php foreach ( $recs as $rec ) : ?>
							<li><strong>&bull;</strong> <?php echo esc_html( $rec ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p style="margin: 0; color: #007a20;">
						✓ <?php esc_html_e( 'Outstanding! Content satisfies all primary Generative Engine Optimization benchmarks.', 'nexura-ai-search-optimizer' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<!-- Footer -->
			<div class="report-footer">
				<div>
					<?php esc_html_e( 'Generated by Nexura AI Search Optimizer &bull; Generative Engine Optimization Infrastructure', 'nexura-ai-search-optimizer' ); ?>
				</div>
				<div>
					<?php esc_html_e( 'Confidential &bull; Page 1 of 1', 'nexura-ai-search-optimizer' ); ?>
				</div>
			</div>
		</div>

		</body>
		</html>
		<?php
	}

	/**
	 * Render bulk library audit report.
	 *
	 * @return void
	 */
	private function render_bulk_report() {
		$results = \NexuraAISearchPro\Bulk_Audit::get_stored_results();
		if ( empty( $results ) ) {
			wp_die( esc_html__( 'No bulk scan results found to export. Please run a scan first.', 'nexura-ai-search-optimizer' ) );
		}

		$site_title  = get_bloginfo( 'name' );
		$site_url    = home_url();
		$total_count = count( $results );
		$scores      = wp_list_pluck( $results, 'score' );
		$avg_score   = round( array_sum( $scores ) / $total_count );
		$date        = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>
				<?php
				/* translators: %s: site title */
				echo esc_html( sprintf( __( 'Site-Wide Library GEO Audit Report - %s', 'nexura-ai-search-optimizer' ), $site_title ) );
				?>
			</title>
			<style>
				@page { size: A4 portrait; margin: 12mm 15mm 15mm 15mm; }
				* { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #1d2327; background: #f0f0f1; margin: 0; padding: 0; font-size: 13px; line-height: 1.5; }
				.no-print-bar { background: #1d2327; color: #fff; padding: 12px 25px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 9999; }
				.no-print-bar .btn-print { background: #2271b1; color: #fff; border: none; padding: 8px 18px; font-size: 14px; font-weight: 600; border-radius: 4px; cursor: pointer; }
				.no-print-bar .btn-close { color: #a7aaad; background: none; border: none; font-size: 13px; cursor: pointer; text-decoration: underline; }
				.report-container { max-width: 900px; margin: 30px auto; background: #fff; padding: 40px; border-radius: 6px; }
				.report-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #2271b1; margin-bottom: 25px; }
				.report-brand h1 { font-size: 22px; margin: 0 0 4px 0; color: #2271b1; }
				.report-brand p { margin: 0; font-size: 12px; color: #646970; }
				.stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
				.stat-card { background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 6px; padding: 20px; text-align: center; }
				.stat-card .num { font-size: 42px; font-weight: 800; color: #2271b1; line-height: 1; }
				.stat-card .lbl { font-size: 12px; text-transform: uppercase; color: #646970; margin-top: 5px; font-weight: 600; }
				table.wide-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; }
				table.wide-table th { background: #f6f7f7; color: #1d2327; text-align: left; padding: 10px 12px; border-bottom: 2px solid #c3c4c7; }
				table.wide-table td { padding: 9px 12px; border-bottom: 1px solid #f0f0f1; vertical-align: middle; }
				.report-footer { margin-top: 35px; padding-top: 15px; border-top: 1px solid #dcdcde; display: flex; justify-content: space-between; font-size: 11px; color: #8c8f94; }
				@media print {
					body { background: #fff; }
					.no-print-bar { display: none !important; }
					.report-container { max-width: 100%; margin: 0; padding: 0; }
					tr { page-break-inside: avoid; }
				}
			</style>
		</head>
		<body>

		<div class="no-print-bar">
			<div><strong><?php esc_html_e( 'Nexura Pro - Full Library GEO Audit Report', 'nexura-ai-search-optimizer' ); ?></strong></div>
			<div style="display: flex; gap: 12px; align-items: center;">
				<button type="button" class="btn-print" onclick="window.print();">🖨️ <?php esc_html_e( 'Print / Save as PDF', 'nexura-ai-search-optimizer' ); ?></button>
				<button type="button" class="btn-close" onclick="window.close();"><?php esc_html_e( 'Close', 'nexura-ai-search-optimizer' ); ?></button>
			</div>
		</div>

		<div class="report-container">
			<div class="report-header">
				<div class="report-brand">
					<h1><?php echo esc_html( $site_title ); ?></h1>
					<p><?php echo esc_html( $site_url ); ?> &bull; <?php esc_html_e( 'Site-Wide Generative Engine Optimization Audit', 'nexura-ai-search-optimizer' ); ?></p>
				</div>
				<div style="text-align: right; font-size: 12px; color: #50575e;">
					<div><strong><?php esc_html_e( 'Date:', 'nexura-ai-search-optimizer' ); ?></strong> <?php echo esc_html( $date ); ?></div>
				</div>
			</div>

			<div class="stat-grid">
				<div class="stat-card">
					<div class="num"><?php echo esc_html( $total_count ); ?></div>
					<div class="lbl"><?php esc_html_e( 'Total Articles Scanned', 'nexura-ai-search-optimizer' ); ?></div>
				</div>
				<div class="stat-card">
					<div class="num"><?php echo esc_html( $avg_score ); ?> <span style="font-size: 18px; color:#646970;">/ 100</span></div>
					<div class="lbl"><?php esc_html_e( 'Site Average GEO Score', 'nexura-ai-search-optimizer' ); ?></div>
				</div>
			</div>

			<table class="wide-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Article Title', 'nexura-ai-search-optimizer' ); ?></th>
						<th style="width: 80px; text-align: center;"><?php esc_html_e( 'Words', 'nexura-ai-search-optimizer' ); ?></th>
						<th style="width: 90px; text-align: center;"><?php esc_html_e( 'Score', 'nexura-ai-search-optimizer' ); ?></th>
						<th style="width: 170px;"><?php esc_html_e( 'AI Readiness Rating', 'nexura-ai-search-optimizer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $results as $row ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $row['title'] ); ?></strong><br>
								<span style="font-size: 11px; color:#646970;"><?php echo esc_url( $row['url'] ); ?></span>
							</td>
							<td style="text-align: center;"><?php echo esc_html( number_format_i18n( $row['word_count'] ) ); ?></td>
							<td style="text-align: center; font-weight: 700; color: #2271b1;"><?php echo esc_html( $row['score'] ); ?> / 100</td>
							<td><?php echo esc_html( $row['rating'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="report-footer">
				<div><?php esc_html_e( 'Nexura AI Search Optimizer Pro &bull; Automated Library Scanner Report', 'nexura-ai-search-optimizer' ); ?></div>
				<div>
					<?php
					/* translators: %d: total items */
					echo esc_html( sprintf( __( 'Total Items: %d', 'nexura-ai-search-optimizer' ), $total_count ) );
					?>
				</div>
			</div>
		</div>

		</body>
		</html>
		<?php
	}
}
