<?php
/**
 * Tab: Dashboard & Status
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Settings;
use NexuraAISearch\Schema_Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$llms_url      = home_url( '/llms.txt' );
$llms_full_url = home_url( '/llms-full.txt' );
$robots_url    = home_url( '/robots.txt' );

$seo_plugins = Schema_Diagnostics::get_active_seo_plugins();

$raw_recent_posts = get_posts( array(
	'numberposts' => 25,
	'post_status' => 'publish',
	'post_type'   => Settings::get_supported_post_types(),
) );
$recent_posts = array_values( array_filter( $raw_recent_posts, function( $p ) {
	return ! \NexuraAISearch\LLMS_Generator::is_excluded_utility_page( $p );
} ) );
$recent_posts = array_slice( $recent_posts, 0, 15 );
?>

<div class="naso-dashboard-grid">
	<!-- Left Column: Core Status & Quick Audit -->
	<div class="naso-main-col">
		<!-- Endpoint Status Cards -->
		<div class="naso-card-deck">
			<!-- Card 1: llms.txt -->
			<div class="naso-stat-card">
				<div class="naso-card-header">
					<span class="dashicons dashicons-media-text"></span>
					<span class="naso-badge <?php echo Settings::get( 'llms_enabled', 1 ) ? 'naso-badge-success' : 'naso-badge-warning'; ?>">
						<?php echo Settings::get( 'llms_enabled', 1 ) ? esc_html__( 'Active', 'nexura-ai-search-optimizer' ) : esc_html__( 'Disabled', 'nexura-ai-search-optimizer' ); ?>
					</span>
				</div>
				<h3>/llms.txt</h3>
				<p class="naso-card-desc"><?php esc_html_e( 'Standardized AI search index for ChatGPT Search, Claude, and Perplexity.', 'nexura-ai-search-optimizer' ); ?></p>
				<div class="naso-endpoint-actions">
					<a href="<?php echo esc_url( $llms_url ); ?>" target="_blank" class="button button-secondary button-small">
						<?php esc_html_e( 'View File', 'nexura-ai-search-optimizer' ); ?> &nearr;
					</a>
					<button type="button" class="button button-small naso-copy-btn" data-clipboard="<?php echo esc_url( $llms_url ); ?>">
						<?php esc_html_e( 'Copy URL', 'nexura-ai-search-optimizer' ); ?>
					</button>
				</div>
			</div>

			<!-- Card 2: llms-full.txt -->
			<div class="naso-stat-card">
				<div class="naso-card-header">
					<span class="dashicons dashicons-media-document"></span>
					<span class="naso-badge <?php echo Settings::get( 'llms_full_enabled', 1 ) ? 'naso-badge-success' : 'naso-badge-warning'; ?>">
						<?php echo Settings::get( 'llms_full_enabled', 1 ) ? esc_html__( 'Active', 'nexura-ai-search-optimizer' ) : esc_html__( 'Disabled', 'nexura-ai-search-optimizer' ); ?>
					</span>
				</div>
				<h3>/llms-full.txt</h3>
				<p class="naso-card-desc"><?php esc_html_e( 'Full markdown documentation index for deep agentic reasoning.', 'nexura-ai-search-optimizer' ); ?></p>
				<div class="naso-endpoint-actions">
					<a href="<?php echo esc_url( $llms_full_url ); ?>" target="_blank" class="button button-secondary button-small">
						<?php esc_html_e( 'View File', 'nexura-ai-search-optimizer' ); ?> &nearr;
					</a>
					<button type="button" class="button button-small naso-copy-btn" data-clipboard="<?php echo esc_url( $llms_full_url ); ?>">
						<?php esc_html_e( 'Copy URL', 'nexura-ai-search-optimizer' ); ?>
					</button>
				</div>
			</div>

			<!-- Card 3: Markdown Output -->
			<div class="naso-stat-card">
				<div class="naso-card-header">
					<span class="dashicons dashicons-editor-code"></span>
					<span class="naso-badge <?php echo Settings::get( 'markdown_output_enabled', 1 ) ? 'naso-badge-success' : 'naso-badge-warning'; ?>">
						<?php echo Settings::get( 'markdown_output_enabled', 1 ) ? esc_html__( 'Active', 'nexura-ai-search-optimizer' ) : esc_html__( 'Disabled', 'nexura-ai-search-optimizer' ); ?>
					</span>
				</div>
				<h3>Clean Markdown</h3>
				<p class="naso-card-desc"><?php esc_html_e( 'Converts posts into pure semantic Markdown via ?format=markdown.', 'nexura-ai-search-optimizer' ); ?></p>
				<div class="naso-endpoint-actions">
					<?php if ( ! empty( $recent_posts ) ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'format', 'markdown', get_permalink( $recent_posts[0]->ID ) ) ); ?>" target="_blank" class="button button-secondary button-small">
							<?php esc_html_e( 'Sample Post', 'nexura-ai-search-optimizer' ); ?> &nearr;
						</a>
					<?php else : ?>
						<span class="description"><?php esc_html_e( 'Publish a post to test', 'nexura-ai-search-optimizer' ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<!-- Card 4: AI Bot Directives -->
			<div class="naso-stat-card">
				<div class="naso-card-header">
					<span class="dashicons dashicons-shield"></span>
					<span class="naso-badge <?php echo Settings::get( 'crawler_policy_enabled', 1 ) ? 'naso-badge-success' : 'naso-badge-warning'; ?>">
						<?php echo Settings::get( 'crawler_policy_enabled', 1 ) ? esc_html__( 'Active', 'nexura-ai-search-optimizer' ) : esc_html__( 'Disabled', 'nexura-ai-search-optimizer' ); ?>
					</span>
				</div>
				<h3>Virtual Robots.txt</h3>
				<p class="naso-card-desc"><?php esc_html_e( 'Allows search bots (OAI-SearchBot, Claude) while blocking aggressive scrapers.', 'nexura-ai-search-optimizer' ); ?></p>
				<div class="naso-endpoint-actions">
					<a href="<?php echo esc_url( $robots_url ); ?>" target="_blank" class="button button-secondary button-small">
						<?php esc_html_e( 'View Robots.txt', 'nexura-ai-search-optimizer' ); ?> &nearr;
					</a>
				</div>
			</div>
		</div>

		<!-- Quick On-Page GEO Audit Widget -->
		<div class="card naso-card">
			<h2><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Quick GEO Content Audit', 'nexura-ai-search-optimizer' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Test any post or page against Princeton University Generative Engine Optimization criteria (BLUF score, fact density, schema, and fluff index).', 'nexura-ai-search-optimizer' ); ?>
			</p>

			<div class="naso-audit-inline-form">
				<label for="naso_quick_post_id" class="screen-reader-text"><?php esc_html_e( 'Select Post', 'nexura-ai-search-optimizer' ); ?></label>
				<select id="naso_quick_post_id" style="max-width: 400px; width: 100%;">
					<?php if ( ! empty( $recent_posts ) ) : ?>
						<?php foreach ( $recent_posts as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?> (<?php echo esc_html( $p->post_type ); ?>)</option>
						<?php endforeach; ?>
					<?php else : ?>
						<option value=""><?php esc_html_e( 'No published content found', 'nexura-ai-search-optimizer' ); ?></option>
					<?php endif; ?>
				</select>
				<button type="button" class="naso-btn-primary" id="naso-run-quick-audit">
					<span class="dashicons dashicons-performance"></span>
					<span><?php esc_html_e( 'Audit for AI Search', 'nexura-ai-search-optimizer' ); ?></span>
				</button>
			</div>

			<!-- AJAX Audit Result Placeholder -->
			<div id="naso-quick-audit-result" style="display: none; margin-top: 20px;"></div>
		</div>
	</div>

	<!-- Right Column: Sidebar Info & Quick Actions -->
	<div class="naso-side-col">
		<!-- SEO Plugin Compatibility Card -->
		<div class="card naso-card">
			<h3><span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span> <?php esc_html_e( 'SEO Plugin Compatibility', 'nexura-ai-search-optimizer' ); ?></h3>
			<?php if ( ! empty( $seo_plugins ) ) : ?>
				<p>
					<?php
					$names = wp_list_pluck( $seo_plugins, 'name' );
					printf(
						/* translators: %s: Plugin names */
						esc_html__( 'Detected active SEO framework: %s.', 'nexura-ai-search-optimizer' ),
						'<strong>' . esc_html( implode( ', ', $names ) ) . '</strong>'
					);
					?>
				</p>
				<div class="naso-compatibility-badge">
					<span class="dashicons dashicons-shield-alt"></span>
					<span><?php esc_html_e( '100% Harmonious Coexistence Active', 'nexura-ai-search-optimizer' ); ?></span>
				</div>
				<p class="description" style="margin-top: 8px;">
					<?php esc_html_e( 'Nexura works seamlessly alongside your SEO plugin. It never alters your meta titles, descriptions, or creates duplicate schemas.', 'nexura-ai-search-optimizer' ); ?>
				</p>
			<?php else : ?>
				<p class="description">
					<?php esc_html_e( 'No dedicated SEO plugin detected. Nexura operates standalone to empower AI engine discoverability.', 'nexura-ai-search-optimizer' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<!-- 2026 AI Crawler, WAF & IndexNow Health Check -->
		<?php
		$has_physical_robots = file_exists( ABSPATH . 'robots.txt' );
		$is_blog_public      = ( '0' !== (string) get_option( 'blog_public', '1' ) );
		$is_cloudflare       = ! empty( $_SERVER['HTTP_CF_RAY'] ) || ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		$indexnow_active     = (bool) Settings::get( 'indexnow_enabled', 1 );
		?>
		<div class="card naso-card">
			<h3><span class="dashicons dashicons-networking" style="color:#2271b1;"></span> <?php esc_html_e( 'AI Crawler & WAF Diagnostics', 'nexura-ai-search-optimizer' ); ?></h3>
			<ul style="margin: 0; padding: 0; list-style: none; font-size: 13px; line-height: 1.6;">
				<li style="margin-bottom: 8px;">
					<?php if ( $is_blog_public ) : ?>
						<span style="color:#00a32a; font-weight:700;">✓</span> <strong><?php esc_html_e( 'Site Indexing:', 'nexura-ai-search-optimizer' ); ?></strong> <?php esc_html_e( 'Public & open to AI search engines.', 'nexura-ai-search-optimizer' ); ?>
					<?php else : ?>
						<span style="color:#d63638; font-weight:700;">✗</span> <strong><?php esc_html_e( 'Site Indexing Blocked:', 'nexura-ai-search-optimizer' ); ?></strong> <?php esc_html_e( 'WordPress "Discourage search engines" is enabled in Settings > Reading!', 'nexura-ai-search-optimizer' ); ?>
					<?php endif; ?>
				</li>
				<li style="margin-bottom: 8px;">
					<?php if ( ! $has_physical_robots ) : ?>
						<span style="color:#00a32a; font-weight:700;">✓</span> <strong><?php esc_html_e( 'Virtual Robots.txt:', 'nexura-ai-search-optimizer' ); ?></strong> <?php esc_html_e( 'No conflicting physical robots.txt file found.', 'nexura-ai-search-optimizer' ); ?>
					<?php else : ?>
						<span style="color:#dba617; font-weight:700;">⚠</span> <strong><?php esc_html_e( 'Physical robots.txt Detected:', 'nexura-ai-search-optimizer' ); ?></strong> <?php esc_html_e( 'A static robots.txt file in your root folder may override WordPress virtual AI directives.', 'nexura-ai-search-optimizer' ); ?>
					<?php endif; ?>
				</li>
				<li style="margin-bottom: 8px;">
					<?php if ( $indexnow_active ) : ?>
						<span style="color:#00a32a; font-weight:700;">✓</span> <strong><?php esc_html_e( 'IndexNow Protocol:', 'nexura-ai-search-optimizer' ); ?></strong> <?php esc_html_e( 'Active for Bing, Copilot & ChatGPT Search.', 'nexura-ai-search-optimizer' ); ?>
					<?php else : ?>
						<span style="color:#dba617; font-weight:700;">⚠</span> <strong><?php esc_html_e( 'IndexNow Protocol:', 'nexura-ai-search-optimizer' ); ?></strong> <?php esc_html_e( 'Disabled in Advanced Settings.', 'nexura-ai-search-optimizer' ); ?>
					<?php endif; ?>
				</li>
				<?php if ( $is_cloudflare ) : ?>
					<li style="margin-top: 8px; padding: 8px 10px; background: #f0f6fc; border-left: 3px solid #2271b1; border-radius: 3px; font-size: 12px;">
						<strong><?php esc_html_e( 'Cloudflare Detected:', 'nexura-ai-search-optimizer' ); ?></strong>
						<?php esc_html_e( 'Ensure "Block AI Bots" / Bot Fight Mode in Cloudflare Security allows OAI-SearchBot, Claude-SearchBot & PerplexityBot.', 'nexura-ai-search-optimizer' ); ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<!-- Quick Maintenance Tools -->
		<div class="card naso-card">
			<h3><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e( 'Maintenance Tools', 'nexura-ai-search-optimizer' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Instantly invalidate cache and regenerate /llms.txt if you modified content.', 'nexura-ai-search-optimizer' ); ?></p>
			<button type="button" class="naso-btn-secondary" id="naso-rebuild-cache-btn" style="width: 100%; margin-bottom: 8px;">
				<span class="dashicons dashicons-update"></span>
				<span><?php esc_html_e( 'Rebuild AI Cache', 'nexura-ai-search-optimizer' ); ?></span>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=nexura-ai-onboarding' ) ); ?>" class="button button-link" style="width: 100%; text-align: center;">
				<?php esc_html_e( 'Re-run 5-Step Setup Wizard', 'nexura-ai-search-optimizer' ); ?>
			</a>
		</div>

		<!-- Pro Upgrade Teaser Box -->
		<?php if ( ! \NexuraAISearch\Admin\Upsell::is_pro() ) : ?>
			<div class="card naso-card naso-pro-highlight-card">
				<h3><?php esc_html_e( 'Unlock Nexura Pro', 'nexura-ai-search-optimizer' ); ?></h3>
				<ul class="naso-feature-list">
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Bulk Scan 500+ Posts at once', 'nexura-ai-search-optimizer' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Scheduled Weekly AI Audits', 'nexura-ai-search-optimizer' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'WooCommerce AI Product Schema', 'nexura-ai-search-optimizer' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Live AI Crawler Hit Tracker', 'nexura-ai-search-optimizer' ); ?></li>
				</ul>
				<a href="<?php echo esc_url( \NexuraAISearch\Admin\Upsell::get_upgrade_url() ); ?>" class="button button-primary" style="width: 100%; text-align: center;">
					<?php esc_html_e( 'Upgrade to Pro &rarr;', 'nexura-ai-search-optimizer' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

