<?php
/**
 * Upgrade to Pro View
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Admin\Upsell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$naso_upgrade_url = Upsell::get_upgrade_url();
?>

<div class="wrap naso-admin-wrap">
	<div class="naso-upgrade-hero" style="text-align: center; padding: 40px 20px; background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; margin-bottom: 25px;">
		<span class="dashicons dashicons-superhero-alt" style="font-size: 52px; width: 52px; height: 52px; color: #2271b1;"></span>
		<h1 style="font-size: 28px; margin: 15px 0 10px 0;"><?php esc_html_e( 'Supercharge Your AI Search Presence with Nexura Pro', 'nexura-ai-search-optimizer' ); ?></h1>
		<p style="font-size: 16px; color: #50575e; max-width: 720px; margin: 0 auto 25px auto;">
			<?php esc_html_e( 'While the Free version gives you essential /llms.txt and single-post scanning, Nexura Pro provides automated enterprise-grade Generative Engine Optimization.', 'nexura-ai-search-optimizer' ); ?>
		</p>
		<a href="<?php echo esc_url( $naso_upgrade_url ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'Upgrade to Nexura Pro Now &rarr;', 'nexura-ai-search-optimizer' ); ?>
		</a>
	</div>

	<!-- Comparison Table -->
	<div class="card naso-card" style="padding: 0; overflow: hidden;">
		<table class="widefat striped" style="border: none;">
			<thead>
				<tr>
					<th style="font-size: 16px; padding: 15px 20px;"><?php esc_html_e( 'Feature Comparison', 'nexura-ai-search-optimizer' ); ?></th>
					<th style="width: 20%; text-align: center; font-size: 16px; padding: 15px 20px;"><?php esc_html_e( 'Nexura Free', 'nexura-ai-search-optimizer' ); ?></th>
					<th style="width: 20%; text-align: center; font-size: 16px; padding: 15px 20px; background: #f0f6fc; color: #135e96;"><strong><?php esc_html_e( 'Nexura Pro ★', 'nexura-ai-search-optimizer' ); ?></strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( '/llms.txt & /llms-full.txt Generation', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Official AI indexing standard for LLMs', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'Clean Markdown Endpoint (?format=markdown)', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Fast HTML-to-Markdown conversion for AI crawlers', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'AI Crawler Policies (OAI-SearchBot, Claude, Perplexity)', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Separate search bots from training scrapers in virtual robots.txt', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'Single-Post On-Page GEO Audit', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Princeton-backed BLUF, stats density, and fluff analysis', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( '1-Click Automated AI Content Rewriter (BYOK)', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Direct integration with OpenAI (GPT-4o), Anthropic (Claude 3.5), & Google (Gemini) at wholesale rates', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-minus" style="color: #8c8f94;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <strong><?php esc_html_e( 'Included', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'Bulk 1-Click Library Scanning', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Batch scan hundreds of posts with progress tracking and scores table', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-minus" style="color: #8c8f94;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <strong><?php esc_html_e( 'Included', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'Automated Scheduled Audits', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Weekly or monthly background scans via WP-Cron with email notifications', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-minus" style="color: #8c8f94;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <strong><?php esc_html_e( 'Included', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'Export PDF & CSV Audit Reports', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Deliver white-label client reports and spreadsheets', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-minus" style="color: #8c8f94;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <strong><?php esc_html_e( 'Included', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'WooCommerce AI Product Optimizer', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Optimizes product specs, stock, and pricing for AI shopping agents', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-minus" style="color: #8c8f94;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <strong><?php esc_html_e( 'Included', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'AI Crawler Hit Tracker & Analytics', 'nexura-ai-search-optimizer' ); ?></strong><br /><span class="description"><?php esc_html_e( 'Track visits from OAI-SearchBot, Claude, and Perplexity in real-time', 'nexura-ai-search-optimizer' ); ?></span></td>
					<td style="text-align: center;"><span class="dashicons dashicons-minus" style="color: #8c8f94;"></span></td>
					<td style="text-align: center; background: #f0f6fc;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <strong><?php esc_html_e( 'Included', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
				<tr>
					<td style="padding: 12px 20px;"><strong><?php esc_html_e( 'Customer Support', 'nexura-ai-search-optimizer' ); ?></strong></td>
					<td style="text-align: center; color: #646970;"><?php esc_html_e( 'WordPress.org Forum', 'nexura-ai-search-optimizer' ); ?></td>
					<td style="text-align: center; background: #f0f6fc; color: #135e96;"><strong><?php esc_html_e( '24/7 Priority Support', 'nexura-ai-search-optimizer' ); ?></strong></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div style="text-align: center; margin: 30px 0;">
		<a href="<?php echo esc_url( $naso_upgrade_url ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'Get Nexura Pro Today &rarr;', 'nexura-ai-search-optimizer' ); ?>
		</a>
	</div>
</div>
