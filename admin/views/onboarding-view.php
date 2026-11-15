<?php
/**
 * 5-Step Onboarding Wizard View
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Settings;
use NexuraAISearch\Schema_Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Step parameter used for tab display only.
$step = isset( $_GET['step'] ) ? max( 1, min( 5, absint( $_GET['step'] ) ) ) : 1;
$settings = Settings::get_all();
$seo_plugins = Schema_Diagnostics::get_active_seo_plugins();
$public_types = get_post_types( array( 'public' => true ), 'objects' );
unset( $public_types['attachment'] );

?>

<div class="wrap naso-wizard-wrap">
	<div class="naso-wizard-container">
		<!-- Header / Logo -->
		<div class="naso-wizard-header">
			<span class="dashicons dashicons-superhero-alt naso-wizard-icon"></span>
			<h1><?php esc_html_e( 'Welcome to Nexura AI Search Optimizer', 'nexura-ai-search-optimizer' ); ?></h1>
			<p><?php esc_html_e( 'Prepare your WordPress site for ChatGPT Search, Claude, Perplexity, and Google AI Overviews in under 60 seconds.', 'nexura-ai-search-optimizer' ); ?></p>
		</div>

		<!-- Step Progress Indicators -->
		<div class="naso-wizard-steps">
			<div class="naso-step-item <?php echo $step >= 1 ? 'is-active' : ''; ?> <?php echo $step > 1 ? 'is-complete' : ''; ?>">
				<div class="naso-step-number">1</div>
				<div class="naso-step-label"><?php esc_html_e( 'Welcome', 'nexura-ai-search-optimizer' ); ?></div>
			</div>
			<div class="naso-step-line <?php echo $step >= 2 ? 'is-active' : ''; ?>"></div>
			<div class="naso-step-item <?php echo $step >= 2 ? 'is-active' : ''; ?> <?php echo $step > 2 ? 'is-complete' : ''; ?>">
				<div class="naso-step-number">2</div>
				<div class="naso-step-label"><?php esc_html_e( 'LLMS.txt', 'nexura-ai-search-optimizer' ); ?></div>
			</div>
			<div class="naso-step-line <?php echo $step >= 3 ? 'is-active' : ''; ?>"></div>
			<div class="naso-step-item <?php echo $step >= 3 ? 'is-active' : ''; ?> <?php echo $step > 3 ? 'is-complete' : ''; ?>">
				<div class="naso-step-number">3</div>
				<div class="naso-step-label"><?php esc_html_e( 'AI Crawlers', 'nexura-ai-search-optimizer' ); ?></div>
			</div>
			<div class="naso-step-line <?php echo $step >= 4 ? 'is-active' : ''; ?>"></div>
			<div class="naso-step-item <?php echo $step >= 4 ? 'is-active' : ''; ?> <?php echo $step > 4 ? 'is-complete' : ''; ?>">
				<div class="naso-step-number">4</div>
				<div class="naso-step-label"><?php esc_html_e( 'Markdown', 'nexura-ai-search-optimizer' ); ?></div>
			</div>
			<div class="naso-step-line <?php echo $step >= 5 ? 'is-active' : ''; ?>"></div>
			<div class="naso-step-item <?php echo $step >= 5 ? 'is-active' : ''; ?>">
				<div class="naso-step-number">5</div>
				<div class="naso-step-label"><?php esc_html_e( 'Finish', 'nexura-ai-search-optimizer' ); ?></div>
			</div>
		</div>

		<!-- Step Content Card -->
		<div class="card naso-wizard-card">
			<?php if ( 1 === $step ) : ?>
				<!-- Step 1: Welcome & Express Button -->
				<h2><?php esc_html_e( 'Step 1: Environment & Compatibility Check', 'nexura-ai-search-optimizer' ); ?></h2>
				<p><?php esc_html_e( 'Nexura automatically scans your WordPress installation to ensure 100% harmonious coexistence with your existing SEO stack.', 'nexura-ai-search-optimizer' ); ?></p>

				<div class="naso-audit-box" style="margin: 20px 0; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
					<h4 style="margin: 0 0 8px 0;"><span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <?php esc_html_e( 'Auto-Detection Results:', 'nexura-ai-search-optimizer' ); ?></h4>
					<p style="margin: 0 0 5px 0;"><strong><?php esc_html_e( 'Site Name:', 'nexura-ai-search-optimizer' ); ?></strong> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
					<p style="margin: 0 0 5px 0;"><strong><?php esc_html_e( 'Permalinks:', 'nexura-ai-search-optimizer' ); ?></strong> <?php echo get_option( 'permalink_structure' ) ? esc_html__( 'Pretty Permalinks Enabled (Optimal)', 'nexura-ai-search-optimizer' ) : esc_html__( 'Plain Permalinks (Fallback Active)', 'nexura-ai-search-optimizer' ); ?></p>
					<p style="margin: 0;">
						<strong><?php esc_html_e( 'SEO Stack:', 'nexura-ai-search-optimizer' ); ?></strong>
						<?php
						if ( ! empty( $seo_plugins ) ) {
							$names = wp_list_pluck( $seo_plugins, 'name' );
							echo esc_html( implode( ', ', $names ) ) . ' &mdash; <span style="color:#00a32a; font-weight:600;">' . esc_html__( 'Coexistence Mode Active (Zero Schema Conflicts)', 'nexura-ai-search-optimizer' ) . '</span>';
						} else {
							esc_html_e( 'No third-party SEO plugin detected. Nexura is ready to provide core AI visibility.', 'nexura-ai-search-optimizer' );
						}
						?>
					</p>
				</div>

				<!-- Express 1-Click Setup Box -->
				<div class="naso-express-box" style="margin: 25px 0; padding: 20px; background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 6px; text-align: center;">
					<h3 style="margin-top: 0; color: #1d2327;"><?php esc_html_e( '⚡ Express 1-Click Setup (Recommended)', 'nexura-ai-search-optimizer' ); ?></h3>
					<p class="description" style="max-width: 600px; margin: 0 auto 15px auto;">
						<?php esc_html_e( 'Immediately activate /llms.txt, allow ChatGPT Search & Claude citation bots, block aggressive scrapers, and enable Clean Markdown with optimal settings in one click.', 'nexura-ai-search-optimizer' ); ?>
					</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="naso_express_setup" />
						<?php wp_nonce_field( 'naso_onboarding_action', 'naso_nonce' ); ?>
						<button type="submit" class="naso-btn-primary naso-btn-hero">
							<?php esc_html_e( 'Activate Best-Practice Defaults & Finish', 'nexura-ai-search-optimizer' ); ?> &rarr;
						</button>
					</form>
				</div>

				<div class="naso-wizard-nav-btns">
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=naso_skip_onboarding' ), 'naso_skip_wizard' ) ); ?>" class="naso-btn-secondary">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Skip Setup & Configure Manually', 'nexura-ai-search-optimizer' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=nexura-ai-onboarding&step=2' ) ); ?>" class="naso-btn-primary">
						<?php esc_html_e( 'Configure Step-by-Step &rarr;', 'nexura-ai-search-optimizer' ); ?>
					</a>
				</div>

			<?php elseif ( 2 === $step ) : ?>
				<!-- Step 2: LLMS.txt -->
				<h2><?php esc_html_e( 'Step 2: Enable LLMS.txt Endpoints', 'nexura-ai-search-optimizer' ); ?></h2>
				<p><?php esc_html_e( 'The /llms.txt file is the 2026 standard for helping AI models locate and digest your website documentation.', 'nexura-ai-search-optimizer' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="naso_save_wizard_step" />
					<input type="hidden" name="step" value="2" />
					<?php wp_nonce_field( 'naso_onboarding_action', 'naso_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Endpoints', 'nexura-ai-search-optimizer' ); ?></th>
							<td>
								<label style="display:block; margin-bottom: 8px;">
									<input type="checkbox" name="llms_enabled" value="1" <?php checked( 1, $settings['llms_enabled'] ); ?> />
									<strong><?php esc_html_e( 'Enable /llms.txt (Standard Content Index)', 'nexura-ai-search-optimizer' ); ?></strong>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="llms_full_enabled" value="1" <?php checked( 1, $settings['llms_full_enabled'] ); ?> />
									<strong><?php esc_html_e( 'Enable /llms-full.txt (Full Documentation Index)', 'nexura-ai-search-optimizer' ); ?></strong>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Post Types', 'nexura-ai-search-optimizer' ); ?></th>
							<td>
								<?php foreach ( $public_types as $type_slug => $type_obj ) : ?>
									<label style="display: inline-block; margin-right: 15px;">
										<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $type_slug ); ?>" <?php checked( in_array( $type_slug, (array) $settings['post_types'], true ) ); ?> />
										<?php echo esc_html( $type_obj->labels->name ); ?>
									</label>
								<?php endforeach; ?>
							</td>
						</tr>
					</table>

					<div class="naso-wizard-nav-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=nexura-ai-onboarding&step=1' ) ); ?>" class="naso-btn-secondary">
							&larr; <?php esc_html_e( 'Back', 'nexura-ai-search-optimizer' ); ?>
						</a>
						<button type="submit" class="naso-btn-primary">
							<?php esc_html_e( 'Save & Continue &rarr;', 'nexura-ai-search-optimizer' ); ?>
						</button>
					</div>
				</form>
				<p style="text-align:center; margin-top:18px;">
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=naso_skip_onboarding' ), 'naso_skip_wizard' ) ); ?>" class="naso-wizard-skip-link">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Skip wizard & configure manually in settings', 'nexura-ai-search-optimizer' ); ?>
					</a>
				</p>

			<?php elseif ( 3 === $step ) : ?>
				<!-- Step 3: AI Crawlers -->
				<h2><?php esc_html_e( 'Step 3: AI Crawler Permissions', 'nexura-ai-search-optimizer' ); ?></h2>
				<p><?php esc_html_e( 'Distinguish between Search Bots (which drive traffic and citations) and aggressive scrapers (which consume bandwidth).', 'nexura-ai-search-optimizer' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="naso_save_wizard_step" />
					<input type="hidden" name="step" value="3" />
					<?php wp_nonce_field( 'naso_onboarding_action', 'naso_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Allow Search Engines', 'nexura-ai-search-optimizer' ); ?></th>
							<td>
								<label style="display:block; margin-bottom: 6px;">
									<input type="checkbox" name="allow_oai_searchbot" value="1" <?php checked( 1, $settings['allow_oai_searchbot'] ); ?> />
									<strong><?php esc_html_e( 'ChatGPT Search (OAI-SearchBot)', 'nexura-ai-search-optimizer' ); ?></strong>
								</label>
								<label style="display:block; margin-bottom: 6px;">
									<input type="checkbox" name="allow_claude_searchbot" value="1" <?php checked( 1, $settings['allow_claude_searchbot'] ); ?> />
									<strong><?php esc_html_e( 'Claude Search (Claude-SearchBot)', 'nexura-ai-search-optimizer' ); ?></strong>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="allow_perplexity" value="1" <?php checked( 1, $settings['allow_perplexity'] ); ?> />
									<strong><?php esc_html_e( 'Perplexity AI (PerplexityBot)', 'nexura-ai-search-optimizer' ); ?></strong>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Scraper Protection', 'nexura-ai-search-optimizer' ); ?></th>
							<td>
								<label style="display:block; margin-bottom: 6px;">
									<input type="checkbox" name="block_bytespider" value="1" <?php checked( 1, $settings['block_bytespider'] ); ?> />
									<?php esc_html_e( 'Block Bytespider (Aggressive TikTok crawler)', 'nexura-ai-search-optimizer' ); ?>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="block_gptbot" value="1" <?php checked( 1, $settings['block_gptbot'] ); ?> />
									<?php esc_html_e( 'Block GPTBot (Model training scraper)', 'nexura-ai-search-optimizer' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<div class="naso-wizard-nav-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=nexura-ai-onboarding&step=2' ) ); ?>" class="naso-btn-secondary">
							&larr; <?php esc_html_e( 'Back', 'nexura-ai-search-optimizer' ); ?>
						</a>
						<button type="submit" class="naso-btn-primary">
							<?php esc_html_e( 'Save & Continue &rarr;', 'nexura-ai-search-optimizer' ); ?>
						</button>
					</div>
				</form>
				<p style="text-align:center; margin-top:18px;">
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=naso_skip_onboarding' ), 'naso_skip_wizard' ) ); ?>" class="naso-wizard-skip-link">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Skip wizard & configure manually in settings', 'nexura-ai-search-optimizer' ); ?>
					</a>
				</p>

			<?php elseif ( 4 === $step ) : ?>
				<!-- Step 4: Markdown Output -->
				<h2><?php esc_html_e( 'Step 4: Clean Markdown & Snippet Controls', 'nexura-ai-search-optimizer' ); ?></h2>
				<p><?php esc_html_e( 'AI systems understand pure Markdown far better than messy HTML containing navigation bars, widgets, and sidebars.', 'nexura-ai-search-optimizer' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="naso_save_wizard_step" />
					<input type="hidden" name="step" value="4" />
					<?php wp_nonce_field( 'naso_onboarding_action', 'naso_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Markdown Support', 'nexura-ai-search-optimizer' ); ?></th>
							<td>
								<label style="display:block; margin-bottom: 8px;">
									<input type="checkbox" name="markdown_output_enabled" value="1" <?php checked( 1, $settings['markdown_output_enabled'] ); ?> />
									<strong><?php esc_html_e( 'Enable ?format=markdown endpoint on all posts/pages', 'nexura-ai-search-optimizer' ); ?></strong>
								</label>
								<label style="display:block;">
									<input type="checkbox" name="geo_snippet_controls" value="1" <?php checked( 1, $settings['geo_snippet_controls'] ); ?> />
									<?php esc_html_e( 'Include max-snippet:-1 meta tag for full Google AI Overview citation length', 'nexura-ai-search-optimizer' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<!-- Baseline GEO Test Widget -->
					<?php
					$sample_post = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
					if ( ! empty( $sample_post ) ) :
					?>
						<div class="naso-audit-inline-card">
							<h4 style="margin: 0 0 6px 0; font-size: 15px;"><span class="dashicons dashicons-performance" style="color:#2271b1; margin-right: 4px;"></span> <?php esc_html_e( 'Instant Baseline GEO Scan', 'nexura-ai-search-optimizer' ); ?></h4>
							<p class="description" style="margin-bottom: 14px; font-size: 13px;">
								<?php esc_html_e( 'Test your latest published post against Princeton University citation benchmarks right now:', 'nexura-ai-search-optimizer' ); ?>
								<strong><?php echo esc_html( get_the_title( $sample_post[0] ) ); ?></strong>
							</p>
							<input type="hidden" id="naso_quick_post_id" value="<?php echo esc_attr( $sample_post[0]->ID ); ?>" />
							<button type="button" class="naso-btn-secondary" id="naso-run-quick-audit">
								<span class="dashicons dashicons-search"></span>
								<?php esc_html_e( 'Run 2-Second Test Scan', 'nexura-ai-search-optimizer' ); ?>
							</button>
							<div id="naso-quick-audit-result" style="display: none; margin-top: 15px;"></div>
						</div>
					<?php endif; ?>

					<div class="naso-wizard-nav-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=nexura-ai-onboarding&step=3' ) ); ?>" class="naso-btn-secondary">
							&larr; <?php esc_html_e( 'Back', 'nexura-ai-search-optimizer' ); ?>
						</a>
						<button type="submit" class="naso-btn-primary">
							<?php esc_html_e( 'Save & Continue &rarr;', 'nexura-ai-search-optimizer' ); ?>
						</button>
					</div>
				</form>
				<p style="text-align:center; margin-top:18px;">
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=naso_skip_onboarding' ), 'naso_skip_wizard' ) ); ?>" class="naso-wizard-skip-link">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Skip wizard & configure manually in settings', 'nexura-ai-search-optimizer' ); ?>
					</a>
				</p>

			<?php elseif ( 5 === $step ) : ?>
				<!-- Step 5: Test & Launch -->
				<div style="text-align: center; padding: 20px 0;">
					<span class="dashicons dashicons-yes-alt" style="font-size: 54px; width: 54px; height: 54px; color: #00a32a;"></span>
					<h2><?php esc_html_e( 'Congratulations! Your Site is AI-Ready', 'nexura-ai-search-optimizer' ); ?></h2>
					<p style="font-size: 15px; color: #50575e; max-width: 600px; margin: 0 auto 25px auto;">
						<?php esc_html_e( 'Your /llms.txt index is active, AI bot policies are live in robots.txt, and Markdown endpoints are fully operational.', 'nexura-ai-search-optimizer' ); ?>
					</p>

					<div style="margin-bottom: 30px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
						<a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" class="naso-btn-secondary">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'View Your Live /llms.txt', 'nexura-ai-search-optimizer' ); ?> &nearr;
						</a>
						<button type="button" class="naso-btn-secondary naso-copy-btn" data-clipboard="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>">
							<span class="dashicons dashicons-clipboard"></span>
							<?php esc_html_e( 'Copy /llms.txt Link', 'nexura-ai-search-optimizer' ); ?>
						</button>
					</div>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="naso_save_wizard_step" />
						<input type="hidden" name="step" value="5" />
						<?php wp_nonce_field( 'naso_onboarding_action', 'naso_nonce' ); ?>
						<button type="submit" class="naso-btn-primary naso-btn-hero">
							<?php esc_html_e( 'Go to Nexura Dashboard &rarr;', 'nexura-ai-search-optimizer' ); ?>
						</button>
					</form>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

