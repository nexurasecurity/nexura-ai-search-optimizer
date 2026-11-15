<?php
/**
 * Tab: LLMS.txt Settings
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$settings = Settings::get_all();
$public_types = get_post_types( array( 'public' => true ), 'objects' );
unset( $public_types['attachment'] );
?>

<div class="card naso-card">
	<h2><span class="dashicons dashicons-media-text"></span> <?php esc_html_e( 'LLMS.txt & LLMS-full.txt Configuration', 'nexura-ai-search-optimizer' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'The /llms.txt standard provides clean, structured Markdown documentation specifically designed for AI reasoning models, ChatGPT Search, Claude, and Perplexity.', 'nexura-ai-search-optimizer' ); ?>
	</p>

	<form method="post" action="options.php" class="naso-form">
		<?php settings_fields( Settings::OPTION_GROUP ); ?>
		<input type="hidden" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[_naso_tab]" value="llms" />

		<table class="form-table" role="presentation">
			<tbody>
				<!-- Enable /llms.txt -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable /llms.txt Index', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_llms_enabled">
							<input type="checkbox" id="naso_llms_enabled" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[llms_enabled]" value="1" <?php checked( 1, $settings['llms_enabled'] ); ?> />
							<?php esc_html_e( 'Serve structured markdown content index at /llms.txt', 'nexura-ai-search-optimizer' ); ?>
						</label>
						<p class="description">
							<?php
							printf(
								/* translators: %s: URL link */
								esc_html__( 'Live URL: %s', 'nexura-ai-search-optimizer' ),
								'<a href="' . esc_url( home_url( '/llms.txt' ) ) . '" target="_blank">' . esc_html( home_url( '/llms.txt' ) ) . '</a>'
							);
							?>
						</p>
					</td>
				</tr>

				<!-- Enable /llms-full.txt -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable /llms-full.txt', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_llms_full_enabled">
							<input type="checkbox" id="naso_llms_full_enabled" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[llms_full_enabled]" value="1" <?php checked( 1, $settings['llms_full_enabled'] ); ?> />
							<?php esc_html_e( 'Serve full article content in clean Markdown at /llms-full.txt', 'nexura-ai-search-optimizer' ); ?>
						</label>
						<p class="description">
							<?php
							printf(
								/* translators: %s: URL link */
								esc_html__( 'Live URL: %s (Bounded for memory safety)', 'nexura-ai-search-optimizer' ),
								'<a href="' . esc_url( home_url( '/llms-full.txt' ) ) . '" target="_blank">' . esc_html( home_url( '/llms-full.txt' ) ) . '</a>'
							);
							?>
						</p>
					</td>
				</tr>

				<!-- Post Types -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Included Post Types', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Included Post Types', 'nexura-ai-search-optimizer' ); ?></legend>
							<?php foreach ( $public_types as $type_slug => $type_obj ) : ?>
								<label style="display: inline-block; margin-right: 15px; margin-bottom: 5px;">
									<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[post_types][]" value="<?php echo esc_attr( $type_slug ); ?>" <?php checked( in_array( $type_slug, (array) $settings['post_types'], true ) ); ?> />
									<?php echo esc_html( $type_obj->labels->name ); ?> (<code><?php echo esc_html( $type_slug ); ?></code>)
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description"><?php esc_html_e( 'Select which content types are exposed to AI engines in your /llms.txt index.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>

				<!-- Include Excerpts -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Include Excerpts', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_include_excerpts">
							<input type="checkbox" id="naso_include_excerpts" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[include_excerpts]" value="1" <?php checked( 1, $settings['include_excerpts'] ); ?> />
							<?php esc_html_e( 'Append a short, clean excerpt under each link in /llms.txt', 'nexura-ai-search-optimizer' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Recommended. Helps AI search crawlers understand page context without needing to fetch the full page.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>

				<!-- Custom Site Summary / Preface -->
				<tr>
					<th scope="row">
						<label for="naso_site_summary"><?php esc_html_e( 'Custom Site Description', 'nexura-ai-search-optimizer' ); ?></label>
					</th>
					<td>
						<textarea id="naso_site_summary" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[site_summary]" rows="3" class="large-text" placeholder="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>"><?php echo esc_textarea( $settings['site_summary'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Displayed in blockquotes at the top of /llms.txt. Leave empty to use your default WordPress tagline.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>

				<!-- Max Links -->
				<tr>
					<th scope="row">
						<label for="naso_max_links"><?php esc_html_e( 'Maximum Links Limit', 'nexura-ai-search-optimizer' ); ?></label>
					</th>
					<td>
						<input type="number" id="naso_max_links" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[max_links]" value="<?php echo esc_attr( $settings['max_links'] ); ?>" min="10" max="500" step="5" class="small-text" />
						<p class="description"><?php esc_html_e( 'Limits the number of recent URLs listed in /llms.txt (between 10 and 500). Default: 100.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>

				<!-- Exclude Specific Posts or Pages -->
				<tr>
					<th scope="row">
						<label for="naso-exclude-search-input">
							<?php esc_html_e( 'Exclude Pages & Posts', 'nexura-ai-search-optimizer' ); ?>
						</label>
					</th>
					<td>
						<div class="naso-post-picker" style="max-width: 520px; position: relative;">
							<!-- Search Input with Search Icon -->
							<div style="position: relative;">
								<span class="dashicons dashicons-search" style="position: absolute; left: 8px; top: 7px; color: #8c8f94; font-size: 18px;"></span>
								<input type="text" id="naso-exclude-search-input" class="regular-text" style="width: 100%; padding-left: 32px; height: 34px; box-sizing: border-box;" placeholder="<?php esc_attr_e( 'Search by page or post title to exclude...', 'nexura-ai-search-optimizer' ); ?>" autocomplete="off" />
								<span class="spinner" id="naso-exclude-search-spinner" style="position: absolute; right: 8px; top: 6px; float: none; margin: 0;"></span>
							</div>

							<!-- Autocomplete Dropdown -->
							<ul id="naso-exclude-search-results" class="naso-autocomplete-dropdown" style="display: none; position: absolute; z-index: 1000; left: 0; right: 0; background: #fff; border: 1px solid #c3c4c7; border-radius: 0 0 4px 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); max-height: 220px; overflow-y: auto; margin: 0; padding: 4px 0; list-style: none;"></ul>

							<!-- Selected Items Container (Tags/Pills) -->
							<div id="naso-excluded-tags" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px;">
								<?php
								$excluded_ids_str = isset( $settings['excluded_post_ids'] ) ? $settings['excluded_post_ids'] : '';
								$excluded_ids_arr = array_filter( array_map( 'absint', explode( ',', $excluded_ids_str ) ) );
								foreach ( $excluded_ids_arr as $eid ) :
									$epost = get_post( $eid );
									if ( ! $epost ) {
										continue;
									}
									?>
									<span class="naso-tag-pill" data-id="<?php echo esc_attr( $eid ); ?>" style="display: inline-flex; align-items: center; gap: 6px; background: #f0f0f1; border: 1px solid #c3c4c7; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #1d2327;">
										<strong><?php echo esc_html( get_the_title( $epost ) ); ?></strong>
										<span style="color: #646970; font-size: 11px;">(<?php echo esc_html( $epost->post_type ); ?> &bull; ID: <?php echo esc_attr( $eid ); ?>)</span>
										<button type="button" class="naso-tag-remove" style="background: none; border: none; padding: 0 2px; cursor: pointer; color: #8c8f94; font-weight: bold; line-height: 1; font-size: 14px;">&times;</button>
									</span>
								<?php endforeach; ?>
							</div>

							<!-- Hidden Input Storing the Comma-Separated IDs -->
							<input type="hidden" id="naso_excluded_post_ids" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[excluded_post_ids]" value="<?php echo esc_attr( $excluded_ids_str ); ?>" />
						</div>

						<p class="description" style="margin-top: 8px;">
							<?php esc_html_e( 'Search by title to add any post or page. Excluded items will not be crawled by AI engines (ChatGPT, Perplexity, Claude), omitted from /llms.txt, and skipped during GEO audits.', 'nexura-ai-search-optimizer' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Save LLMS Settings', 'nexura-ai-search-optimizer' ) ); ?>
	</form>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

