<?php
/**
 * Tab: AI Crawlers & Robots.txt Directives
 *
 * @package NexuraAISearch\Admin
 */

use NexuraAISearch\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$settings = Settings::get_all();
?>

<div class="card naso-card">
	<h2><span class="dashicons dashicons-shield"></span> <?php esc_html_e( 'AI Crawler Policy & Virtual Robots.txt', 'nexura-ai-search-optimizer' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Control which AI search engines and autonomous crawlers can discover, index, and cite your content. Nexura strictly separates real-time Search Engines from bulk LLM Training Scrapers.', 'nexura-ai-search-optimizer' ); ?>
	</p>

	<form method="post" action="options.php" class="naso-form">
		<?php settings_fields( Settings::OPTION_GROUP ); ?>
		<input type="hidden" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[_naso_tab]" value="crawlers" />

		<!-- Enable Crawler Policy -->
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'AI Crawler Management', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_crawler_policy_enabled">
							<input type="checkbox" id="naso_crawler_policy_enabled" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[crawler_policy_enabled]" value="1" <?php checked( 1, $settings['crawler_policy_enabled'] ); ?> />
							<strong><?php esc_html_e( 'Inject AI Bot Directives into WordPress Virtual Robots.txt', 'nexura-ai-search-optimizer' ); ?></strong>
						</label>
						<p class="description"><?php esc_html_e( 'Safely appends AI bot rules to your existing virtual robots.txt without modifying physical files on the server.', 'nexura-ai-search-optimizer' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		<!-- Section 1: Search Visibility Crawlers -->
		<h3><?php esc_html_e( '1. AI Search Visibility Bots (Allow for Citations)', 'nexura-ai-search-optimizer' ); ?></h3>
		<p class="description"><?php esc_html_e( 'These user-facing search bots power conversational AI answers. Allowing them enables ChatGPT, Claude, and Perplexity to link directly to your website as a cited source.', 'nexura-ai-search-optimizer' ); ?></p>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'OpenAI Search (ChatGPT)', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_oai_searchbot">
							<input type="checkbox" id="naso_allow_oai_searchbot" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_oai_searchbot]" value="1" <?php checked( 1, $settings['allow_oai_searchbot'] ); ?> />
							<code>User-agent: OAI-SearchBot</code> (<?php esc_html_e( 'ChatGPT Search engine citations', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Anthropic Claude Search', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_claude_searchbot">
							<input type="checkbox" id="naso_allow_claude_searchbot" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_claude_searchbot]" value="1" <?php checked( 1, $settings['allow_claude_searchbot'] ); ?> />
							<code>User-agent: Claude-SearchBot</code> (<?php esc_html_e( 'Claude live web search & citation links', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Perplexity AI', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_perplexity">
							<input type="checkbox" id="naso_allow_perplexity" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_perplexity]" value="1" <?php checked( 1, $settings['allow_perplexity'] ); ?> />
							<code>User-agent: PerplexityBot</code> (<?php esc_html_e( 'Perplexity conversational search engine', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Google AI Overviews', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_googlebot">
							<input type="checkbox" id="naso_allow_googlebot" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_googlebot]" value="1" <?php checked( 1, $settings['allow_googlebot'] ); ?> />
							<code>User-agent: Googlebot</code> (<?php esc_html_e( 'Google Search & Google AI Overviews citations', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Apple Intelligence', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_applebot">
							<input type="checkbox" id="naso_allow_applebot" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_applebot]" value="1" <?php checked( 1, $settings['allow_applebot'] ); ?> />
							<code>User-agent: Applebot</code> (<?php esc_html_e( 'Siri & Apple Intelligence spotlight search', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Live User-Triggered AI Agents', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_chatgpt_user">
							<input type="checkbox" id="naso_allow_chatgpt_user" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_chatgpt_user]" value="1" <?php checked( 1, $settings['allow_chatgpt_user'] ); ?> />
							<code>ChatGPT-User, Claude-User, Perplexity-User</code> (<?php esc_html_e( 'On-demand live web fetching when a human prompts an AI assistant', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'DuckAssist, Amazon & You.com', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_allow_duckassist">
							<input type="checkbox" id="naso_allow_duckassist" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[allow_duckassist]" value="1" <?php checked( 1, $settings['allow_duckassist'] ); ?> />
							<code>DuckAssistBot, Amazonbot, YouBot</code> (<?php esc_html_e( 'DuckDuckGo AI Answers, Amazon Rufus AI & You.com citations', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		<!-- Section 2: AI Training Scrapers -->
		<h3><?php esc_html_e( '2. Bulk AI Model Training Scrapers', 'nexura-ai-search-optimizer' ); ?></h3>
		<p class="description"><?php esc_html_e( 'These scrapers crawl the web to train foundation models (not for real-time citations). You can selectively disallow them to protect bandwidth and intellectual property.', 'nexura-ai-search-optimizer' ); ?></p>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'ByteDance / TikTok', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_block_bytespider">
							<input type="checkbox" id="naso_block_bytespider" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[block_bytespider]" value="1" <?php checked( 1, $settings['block_bytespider'] ); ?> />
							<code>Disallow: Bytespider</code> (<?php esc_html_e( 'Recommended: High frequency scraping bot that rarely provides traffic', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'OpenAI Training Bot', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_block_gptbot">
							<input type="checkbox" id="naso_block_gptbot" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[block_gptbot]" value="1" <?php checked( 1, $settings['block_gptbot'] ); ?> />
							<code>Disallow: GPTBot</code> (<?php esc_html_e( 'Blocks bulk model training for future GPT releases', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Anthropic Training Bot', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_block_claudebot">
							<input type="checkbox" id="naso_block_claudebot" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[block_claudebot]" value="1" <?php checked( 1, $settings['block_claudebot'] ); ?> />
							<code>Disallow: ClaudeBot</code> (<?php esc_html_e( 'Blocks bulk training scraper for Claude models', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Google Gemini Training', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_block_google_extended">
							<input type="checkbox" id="naso_block_google_extended" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[block_google_extended]" value="1" <?php checked( 1, $settings['block_google_extended'] ); ?> />
							<code>Disallow: Google-Extended</code>
						</label>
						<p class="description">
							<strong><?php esc_html_e( 'Official Google Note:', 'nexura-ai-search-optimizer' ); ?></strong>
							<?php esc_html_e( 'Google-Extended blocks Gemini model training, but per Google documentation, it does NOT block your content from appearing in Google AI Overviews.', 'nexura-ai-search-optimizer' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Apple AI Training', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_block_applebot_extended">
							<input type="checkbox" id="naso_block_applebot_extended" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[block_applebot_extended]" value="1" <?php checked( 1, $settings['block_applebot_extended'] ); ?> />
							<code>Disallow: Applebot-Extended</code> (<?php esc_html_e( 'Blocks Apple foundation model training while keeping Siri search active', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Meta AI Training (Llama)', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_block_meta_external">
							<input type="checkbox" id="naso_block_meta_external" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[block_meta_external]" value="1" <?php checked( 1, $settings['block_meta_external'] ); ?> />
							<code>Disallow: Meta-ExternalAgent</code> (<?php esc_html_e( 'Blocks Meta Llama foundation model training scraper', 'nexura-ai-search-optimizer' ); ?>)
						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		<!-- Section 3: Google AI Overviews Snippet Controls -->
		<h3><?php esc_html_e( '3. Google AI Overviews & Snippet Controls', 'nexura-ai-search-optimizer' ); ?></h3>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Snippet Length Meta Tag', 'nexura-ai-search-optimizer' ); ?></th>
					<td>
						<label for="naso_geo_snippet_controls">
							<input type="checkbox" id="naso_geo_snippet_controls" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[geo_snippet_controls]" value="1" <?php checked( 1, $settings['geo_snippet_controls'] ); ?> />
							<?php esc_html_e( 'Output robots max-snippet meta tag in header', 'nexura-ai-search-optimizer' ); ?>
						</label>
						<div style="margin-top: 8px;">
							<label for="naso_max_snippet_length">
								<?php esc_html_e( 'Max Snippet Length:', 'nexura-ai-search-optimizer' ); ?>
								<input type="number" id="naso_max_snippet_length" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[max_snippet_length]" value="<?php echo esc_attr( $settings['max_snippet_length'] ); ?>" class="small-text" />
								<span class="description"><?php esc_html_e( '(-1 for unrestricted citation length, or positive number of characters)', 'nexura-ai-search-optimizer' ); ?></span>
							</label>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Save Crawler Policy', 'nexura-ai-search-optimizer' ) ); ?>
	</form>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

