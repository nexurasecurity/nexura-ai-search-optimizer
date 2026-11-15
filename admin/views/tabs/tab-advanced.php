<?php
/**
 * Tab: Advanced Settings & Cache
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$naso_settings = Settings::get_all();
?>

<div class="card naso-card">
	<h2><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Advanced Optimization & System Settings', 'nexura-ai-search-optimizer' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Configure caching intervals, Markdown headers, and data lifecycle management.', 'nexura-ai-search-optimizer' ); ?>
	</p>

	<form method="post" action="options.php" class="naso-form">
		<?php settings_fields( Settings::OPTION_GROUP ); ?>
		<input type="hidden" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[_naso_tab]" value="advanced" />

		<table class="form-table" role="presentation">
			<tbody>
				<!-- Cache TTL -->
				<tr>
					<th scope="row">
						<label for="naso_cache_ttl_hours"><?php esc_html_e( 'Cache Expiration (Hours)', 'nexura-ai-search-optimizer' ); ?></label>
					</th>
					<td>
						<input type="number" id="naso_cache_ttl_hours" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[cache_ttl_hours]" value="<?php echo esc_attr( $naso_settings['cache_ttl_hours'] ); ?>" min="1" max="168" class="small-text" />
						<p class="description"><?php esc_html_e( 'How long generated /llms.txt and /llms-full.txt content is cached in transients. Default: 24 hours. (Cache automatically flushes when you publish or edit posts).', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>

				<!-- Markdown Output -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Clean Markdown Endpoint', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_markdown_output_enabled">
							<input type="checkbox" id="naso_markdown_output_enabled" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[markdown_output_enabled]" value="1" <?php checked( 1, $naso_settings['markdown_output_enabled'] ); ?> />
							<?php esc_html_e( 'Enable ?format=markdown & .md URL endpoints for singular posts and pages', 'nexura-ai-search-optimizer' ); ?>
						</label>
						<br />
						<label for="naso_markdown_head_link" style="margin-top: 8px; display: inline-block;">
							<input type="checkbox" id="naso_markdown_head_link" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[markdown_head_link]" value="1" <?php checked( 1, $naso_settings['markdown_head_link'] ); ?> />
							<?php esc_html_e( 'Output <link rel="alternate" type="text/markdown">, <link rel="describedby"> & HTTP Link headers (llmstxt.org v2)', 'nexura-ai-search-optimizer' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Informs AI bots like ChatGPT and Perplexity that a clean, semantic markdown representation of the page is available.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>

				<!-- IndexNow Instant AI Indexing -->
				<?php
				require_once NEXURA_AI_SEARCH_DIR . 'includes/class-indexnow.php';
				$naso_indexnow_key  = \NexuraAISearch\IndexNow::get_api_key();
				$naso_indexnow_ping = get_option( 'naso_indexnow_last_ping', array() );
				?>
				<tr>
					<th scope="row"><?php esc_html_e( 'IndexNow Instant AI Indexing', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_indexnow_enabled">
							<input type="checkbox" id="naso_indexnow_enabled" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[indexnow_enabled]" value="1" <?php checked( 1, $naso_settings['indexnow_enabled'] ); ?> />
							<strong><?php esc_html_e( 'Automatically ping IndexNow (Bing, Microsoft Copilot & ChatGPT Search) when content is published or AI-optimized', 'nexura-ai-search-optimizer' ); ?></strong>
						</label>
						<p class="description" style="margin-top: 6px;">
							<?php esc_html_e( 'Verification Key URL:', 'nexura-ai-search-optimizer' ); ?>
							<code><a href="<?php echo esc_url( home_url( '/' . $naso_indexnow_key . '.txt' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/' . $naso_indexnow_key . '.txt' ) ); ?></a></code>
							<?php if ( ! empty( $naso_indexnow_ping['url'] ) ) : ?>
								<br />
								<span style="color:#00a32a; font-size:12px;">
									✓ <?php esc_html_e( 'Last Ping:', 'nexura-ai-search-optimizer' ); ?>
									<strong><?php echo esc_html( $naso_indexnow_ping['url'] ); ?></strong>
									(<?php echo esc_html( $naso_indexnow_ping['time'] ); ?>)
								</span>
							<?php endif; ?>
						</p>
					</td>
				</tr>

				<!-- Uninstall Data Cleanliness -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Data Removal on Uninstall', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_delete_data_on_uninstall">
							<input type="checkbox" id="naso_delete_data_on_uninstall" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[delete_data_on_uninstall]" value="1" <?php checked( 1, $naso_settings['delete_data_on_uninstall'] ); ?> />
							<span style="color: #b32d2e;"><?php esc_html_e( 'Completely wipe all Nexura settings and transients when plugin is deleted from Plugins screen.', 'nexura-ai-search-optimizer' ); ?></span>
						</label>
						<p class="description"><?php esc_html_e( 'Leave unchecked if you want to keep your configuration for future reinstalls.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Save Advanced Settings', 'nexura-ai-search-optimizer' ) ); ?>
	</form>

	<hr style="margin-top: 30px;" />

	<h3><?php esc_html_e( 'System Tools & Diagnostics', 'nexura-ai-search-optimizer' ); ?></h3>
	<p class="description"><?php esc_html_e( 'Manually flush transient data and rewrite endpoints.', 'nexura-ai-search-optimizer' ); ?></p>
	<div style="display: flex; gap: 10px; flex-wrap: wrap;">
		<button type="button" class="naso-btn-secondary" id="naso-flush-cache-btn">
			<span class="dashicons dashicons-trash"></span>
			<span><?php esc_html_e( 'Flush AI Transients Cache', 'nexura-ai-search-optimizer' ); ?></span>
		</button>
	</div>
</div>
