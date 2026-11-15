<?php
/**
 * Tab: GEO Content Audit Tool
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$post_types = Settings::get( 'post_types', array( 'post', 'page' ) );
$raw_posts  = get_posts( array(
	'numberposts' => 60,
	'post_status' => 'publish',
	'post_type'   => $post_types,
) );

// Exclude transactional / system utility pages (Cart, Checkout, Account, Policies)
$posts = array_filter( $raw_posts, function( $p ) {
	return ! \NexuraAISearch\LLMS_Generator::is_excluded_utility_page( $p );
} );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Query parameter used for post selection only.
$selected_post_id    = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
$is_selected_utility = $selected_post_id && \NexuraAISearch\LLMS_Generator::is_excluded_utility_page( $selected_post_id );
?>

<div class="card naso-card">
	<h2><span class="dashicons dashicons-chart-pie"></span> <?php esc_html_e( 'Generative Engine Optimization (GEO) On-Page Scanner', 'nexura-ai-search-optimizer' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Analyze individual posts and pages against empirical citation criteria researched by Princeton University, Georgia Tech, and modern AI Search standards.', 'nexura-ai-search-optimizer' ); ?>
	</p>

	<?php if ( $is_selected_utility ) : ?>
		<div class="notice notice-info inline" style="margin: 15px 0; padding: 12px 15px;">
			<p>
				<strong>ℹ️ <?php esc_html_e( 'System Utility Page:', 'nexura-ai-search-optimizer' ); ?></strong>
				<?php esc_html_e( 'The selected page is a WooCommerce or system utility page (Cart, Checkout, Account, or Policy). These pages are intentionally excluded from AI search crawling and do not require GEO optimization.', 'nexura-ai-search-optimizer' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Selector Toolbar -->
	<div class="naso-audit-toolbar">
		<label for="naso_audit_post_select" class="screen-reader-text"><?php esc_html_e( 'Select Post to Scan', 'nexura-ai-search-optimizer' ); ?></label>
		<select id="naso_audit_post_select" class="naso-select-post" style="max-width: 500px; width: 100%;">
			<option value=""><?php esc_html_e( '-- Select a Published Post or Page --', 'nexura-ai-search-optimizer' ); ?></option>
			<?php foreach ( $posts as $p ) : ?>
				<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( $p->ID, $selected_post_id ); ?>>
					<?php echo esc_html( get_the_title( $p ) ); ?> (<?php echo esc_html( $p->post_type ); ?> &bull; ID: <?php echo esc_attr( $p->ID ); ?>)
				</option>
			<?php endforeach; ?>
		</select>
		<button type="button" class="naso-btn-primary" id="naso-start-audit-btn">
			<span class="dashicons dashicons-search"></span>
			<span><?php esc_html_e( 'Run GEO Audit', 'nexura-ai-search-optimizer' ); ?></span>
		</button>
		<span class="spinner" id="naso-audit-spinner" style="float: none; margin: 0 10px;"></span>
	</div>

	<!-- Results Container -->
	<div id="naso-audit-results" style="display: none; margin-top: 25px;">
		<!-- Result Header -->
		<div class="naso-result-header">
			<div class="naso-score-box">
				<div class="naso-score-number" id="naso-audit-score">0</div>
				<div class="naso-score-label"><?php esc_html_e( 'GEO Score (out of 100)', 'nexura-ai-search-optimizer' ); ?></div>
			</div>
			<div class="naso-score-details">
				<h3 id="naso-audit-title"></h3>
				<p>
					<span class="naso-badge" id="naso-audit-badge"></span>
					<span id="naso-audit-word-count" style="margin-left: 10px; color: #646970;"></span>
				</p>
			</div>
			<div class="naso-result-actions" style="margin-left: auto; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
				<div id="naso-audit-action-slot">
					<button type="button" id="naso-audit-ai-prompt-btn" class="naso-btn-primary naso-trigger-ai-prompt">
						<span class="dashicons dashicons-rest-api"></span>
						<span><?php esc_html_e( '1-Click AI Fix Prompt', 'nexura-ai-search-optimizer' ); ?></span>
					</button>
				</div>
				<a href="#" id="naso-audit-pdf-btn" target="_blank" class="naso-btn-secondary" style="text-decoration: none;">
					<span class="dashicons dashicons-media-document"></span>
					<span><?php esc_html_e( 'Export PDF Report', 'nexura-ai-search-optimizer' ); ?></span>
				</a>
			</div>
		</div>

		<!-- Breakdown Grid -->
		<h3 style="margin-top: 25px;"><?php esc_html_e( 'Scoring Breakdown & Criteria Evaluation', 'nexura-ai-search-optimizer' ); ?></h3>
		<div class="naso-breakdown-grid" id="naso-audit-breakdown"></div>

		<!-- Actionable Recommendations -->
		<h3 style="margin-top: 25px;"><?php esc_html_e( 'Actionable GEO Improvements', 'nexura-ai-search-optimizer' ); ?></h3>
		<ul class="naso-recommendations-list" id="naso-audit-recommendations"></ul>
	</div>
</div>

<!-- Bulk Audit Upsell Card -->
<?php if ( ! \NexuraAISearch\Admin\Upsell::is_pro() ) : ?>
	<div class="card naso-card naso-pro-banner-card" style="margin-top: 20px;">
		<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
			<div>
				<h3 style="margin-top: 0; color: #1d2327;">
					<span class="dashicons dashicons-star-filled" style="color: #f0b849;"></span>
					<?php esc_html_e( 'Need to Audit Your Entire Library at Once?', 'nexura-ai-search-optimizer' ); ?>
				</h3>
				<p style="margin-bottom: 0; max-width: 700px; color: #50575e;">
					<?php esc_html_e( 'Nexura Pro features Automated Bulk Scanning, Weekly Scheduled Audits, CSV / PDF Reports, and Priority E-E-A-T Schema Enhancement.', 'nexura-ai-search-optimizer' ); ?>
				</p>
			</div>
			<a href="<?php echo esc_url( \NexuraAISearch\Admin\Upsell::get_upgrade_url() ); ?>" class="button button-primary">
				<?php esc_html_e( 'Unlock Bulk Audits &rarr;', 'nexura-ai-search-optimizer' ); ?>
			</a>
		</div>
	</div>
<?php endif; ?>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

