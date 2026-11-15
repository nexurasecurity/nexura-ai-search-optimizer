<?php
/**
 * Post & Product Edit Screen Meta Box
 *
 * Displays on-page Generative Engine Optimization diagnostics, actionable fix instructions,
 * and 1-Click AI Fix Prompt generation inside the WordPress editor.
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Edit Screen Meta Box and AJAX prompt fetching.
 */
class Meta_Box {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_meta_box_assets' ) );
		add_action( 'wp_ajax_naso_get_ai_fix_prompt', array( $this, 'ajax_get_ai_fix_prompt' ) );
	}

	/**
	 * Register Meta Box on supported post types.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		$post_types = Settings::get( 'post_types', array( 'post', 'page' ) );
		if ( class_exists( 'WooCommerce' ) && ! in_array( 'product', $post_types, true ) ) {
			$post_types[] = 'product';
		}

		foreach ( $post_types as $pt ) {
			add_meta_box(
				'naso_geo_meta_box',
				__( 'Nexura AI Search Optimizer (GEO Readiness)', 'nexura-ai-search-optimizer' ),
				array( $this, 'render_meta_box' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render the Meta Box UI.
	 *
	 * @param \WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		$post_id           = $post->ID;
		$is_pro            = Settings::is_pro();
		$is_system_utility = ( ! empty( $post->post_name ) && in_array( $post->post_name, array( 'cart', 'checkout', 'my-account', 'myaccount', 'order-pay', 'order-received', 'lost-password', 'password-reset', 'reset-password' ), true ) );
		$is_user_excluded  = ( '1' === get_post_meta( $post_id, '_naso_exclude_from_ai', true ) );

		// System / E-Commerce utility pages (Cart, Checkout, Account) are hard-excluded from AI search.
		if ( $is_system_utility ) {
			?>
			<div class="naso-metabox-container naso-utility-container" data-post-id="<?php echo esc_attr( $post_id ); ?>" style="padding: 16px; background: #f6f7f7; border: 1px solid #dcdcde; border-left: 4px solid #2271b1; border-radius: 4px;">
				<div style="display: flex; align-items: flex-start; gap: 14px;">
					<span class="dashicons dashicons-info-outline" style="font-size: 28px; width: 28px; height: 28px; color: #2271b1; flex-shrink: 0; margin-top: 2px;"></span>
					<div>
						<h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600; color: #1d2327;">
							<?php esc_html_e( 'System / E-Commerce Utility Page (Excluded from AI Search)', 'nexura-ai-search-optimizer' ); ?>
						</h4>
						<p style="margin: 0 0 8px 0; font-size: 13px; color: #50575e; line-height: 1.5;">
							<?php esc_html_e( 'This page (Cart, Checkout, Account) serves functional user sessions. AI engines (ChatGPT Search, Perplexity, Claude) and web crawlers are instructed not to index transactional checkout funnels to protect customer privacy and avoid indexing empty shopping sessions.', 'nexura-ai-search-optimizer' ); ?>
						</p>
						<span class="naso-badge" style="background: #e2e4e7; color: #50575e; font-size: 11px; padding: 3px 8px; border-radius: 3px; font-weight: 600; display: inline-block;">
							<?php esc_html_e( 'Automatically excluded from /llms.txt & GEO scoring', 'nexura-ai-search-optimizer' ); ?>
						</span>
					</div>
				</div>
			</div>
			<?php
			return;
		}

		wp_nonce_field( 'naso_meta_box_save', 'naso_meta_box_nonce' );

		// User manually excluded this post from AI search
		if ( $is_user_excluded ) {
			?>
			<div class="naso-metabox-container naso-user-excluded-container" data-post-id="<?php echo esc_attr( $post_id ); ?>" style="padding: 16px; background: #fff8e5; border: 1px solid #dba617; border-left: 4px solid #dba617; border-radius: 4px;">
				<div style="display: flex; align-items: flex-start; gap: 14px;">
					<span class="dashicons dashicons-hidden" style="font-size: 28px; width: 28px; height: 28px; color: #dba617; flex-shrink: 0; margin-top: 2px;"></span>
					<div>
						<h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600; color: #1d2327;">
							<?php esc_html_e( 'Manually Excluded from AI Search & GEO Audits', 'nexura-ai-search-optimizer' ); ?>
							<span class="naso-badge" style="background:#2271b1; color:#fff; font-size:10px; padding:2px 6px; border-radius:3px; margin-left:6px;"><?php esc_html_e( 'PRO', 'nexura-ai-search-optimizer' ); ?></span>
						</h4>
						<p style="margin: 0 0 10px 0; font-size: 13px; color: #50575e; line-height: 1.5;">
							<?php esc_html_e( 'You have explicitly excluded this content from /llms.txt, AI crawlers (ChatGPT Search, Perplexity, Claude), and GEO audits.', 'nexura-ai-search-optimizer' ); ?>
						</p>
						<label for="naso_exclude_from_ai" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 12px; cursor: pointer; color: #1d2327;">
							<input type="checkbox" id="naso_exclude_from_ai" name="naso_exclude_from_ai" value="1" checked="checked" />
							<?php esc_html_e( 'Keep excluded from AI Search & /llms.txt (Uncheck and update page to re-enable)', 'nexura-ai-search-optimizer' ); ?>
						</label>
					</div>
				</div>
			</div>
			<?php
			return;
		}

		$score       = get_post_meta( $post_id, '_naso_geo_score', true );
		$badge_class = get_post_meta( $post_id, '_naso_geo_badge', true );
		$rating      = get_post_meta( $post_id, '_naso_geo_rating', true );
		$recs        = get_post_meta( $post_id, '_naso_geo_recs', true );

		if ( ! is_array( $recs ) ) {
			$recs = array();
		}

		$has_scanned = ( '' !== $score && false !== $score );
		$score_int   = $has_scanned ? absint( $score ) : 0;
		if ( empty( $badge_class ) ) {
			$badge_class = ( $score_int >= 80 ) ? 'naso-badge-success' : ( ( $score_int >= 50 ) ? 'naso-badge-warning' : 'naso-badge-danger' );
		}

		$audit_url = admin_url( 'admin.php?page=nexura-ai-search-audit&post_id=' . $post_id );
		$pdf_url   = wp_nonce_url( admin_url( 'admin-post.php?action=naso_export_pdf&post_id=' . $post_id ), 'naso_export_pdf', 'nonce' );

		?>
		<div class="naso-metabox-container" data-post-id="<?php echo esc_attr( $post_id ); ?>">
			<!-- Meta Box Header Card -->
			<div class="naso-mb-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; padding: 15px; background: #f8f9fa; border: 1px solid #dcdcde; border-radius: 6px;">
				<div style="display: flex; align-items: center; gap: 15px;">
					<div class="naso-mb-score-box" style="text-align: center; min-width: 80px;">
						<span class="naso-mb-score" style="font-size: 36px; font-weight: 800; line-height: 1; color: <?php echo $score_int >= 80 ? '#00a32a' : ( $score_int >= 50 ? '#dba617' : '#d63638' ); ?>;">
							<?php echo $has_scanned ? esc_html( $score_int ) : '--'; ?>
						</span>
						<div style="font-size: 11px; color: #646970; text-transform: uppercase;"><?php esc_html_e( 'GEO Score', 'nexura-ai-search-optimizer' ); ?></div>
					</div>
					<div>
						<h4 style="margin: 0 0 4px 0; font-size: 15px;">
							<?php echo $has_scanned ? esc_html( $rating ) : esc_html__( 'Not Scanned Yet', 'nexura-ai-search-optimizer' ); ?>
						</h4>
						<p style="margin: 0; font-size: 12px; color: #50575e;">
							<?php esc_html_e( 'Measures citation likelihood in ChatGPT Search, Perplexity, Claude, and Google AI Overviews.', 'nexura-ai-search-optimizer' ); ?>
						</p>
					</div>
				</div>

				<div class="naso-mb-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
					<button type="button" class="naso-btn-primary naso-mb-scan-btn" data-post-id="<?php echo esc_attr( $post_id ); ?>">
						<span class="dashicons dashicons-search"></span>
						<span><?php esc_html_e( 'Run GEO Scan', 'nexura-ai-search-optimizer' ); ?></span>
					</button>

					<div class="naso-mb-action-slot">
						<?php if ( $has_scanned && $score >= 100 && empty( $recs ) ) : ?>
							<span class="naso-badge naso-badge-optimal naso-mb-optimized-badge" style="padding: 6px 12px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; background: #e7f8ed; color: #007a22; border: 1px solid #68de7c;">
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> <?php esc_html_e( 'Fully Optimized', 'nexura-ai-search-optimizer' ); ?>
							</span>
						<?php else : ?>
							<button type="button" class="naso-btn-secondary naso-trigger-ai-prompt" data-id="<?php echo esc_attr( $post_id ); ?>">
								<span class="dashicons dashicons-rest-api" style="color: #2271b1;"></span>
								<span><?php esc_html_e( '1-Click AI Fix Prompt', 'nexura-ai-search-optimizer' ); ?></span>
							</button>
						<?php endif; ?>
					</div>

					<?php if ( $has_scanned ) : ?>
						<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="naso-btn-secondary" style="text-decoration: none;">
							<span class="dashicons dashicons-media-document"></span>
							<span><?php esc_html_e( 'PDF Report', 'nexura-ai-search-optimizer' ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Weaknesses & Fix Instructions -->
			<div class="naso-mb-findings" style="margin-top: 15px;">
				<h4 style="margin: 0 0 8px 0; font-size: 13px; color: #1d2327;">
					<span class="dashicons dashicons-warning" style="color: #dba617; vertical-align: middle;"></span>
					<?php esc_html_e( 'Identified GEO Issues & Optimization Steps:', 'nexura-ai-search-optimizer' ); ?>
				</h4>

				<div class="naso-mb-issues-container">
					<?php if ( ! empty( $recs ) ) : ?>
						<ul class="naso-recommendations-list" style="margin: 0; padding-left: 20px;">
							<?php foreach ( $recs as $rec ) : ?>
								<li style="margin-bottom: 6px; font-size: 13px; color: #2c3338;">
									<strong>&bull;</strong> <?php echo esc_html( $rec ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php elseif ( $has_scanned ) : ?>
						<p style="margin: 0; color: #00a32a; font-weight: 500;">
							✓ <?php esc_html_e( 'Exemplary! This content satisfies core Generative Engine Optimization benchmarks.', 'nexura-ai-search-optimizer' ); ?>
						</p>
					<?php else : ?>
						<p style="margin: 0; color: #646970; font-style: italic;">
							<?php esc_html_e( 'Click "Run GEO Scan" above to evaluate this content against Princeton University citation metrics.', 'nexura-ai-search-optimizer' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Exclusion Control -->
			<div class="naso-mb-exclude-toggle" style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #dcdcde; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
				<label for="naso_exclude_from_ai" style="display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 500; cursor: pointer; color: #50575e;">
					<input type="checkbox" id="naso_exclude_from_ai" name="naso_exclude_from_ai" value="1" <?php checked( $is_user_excluded ); ?> />
					<span><?php esc_html_e( 'Exclude this post/page from AI Search engines, /llms.txt, and GEO audits', 'nexura-ai-search-optimizer' ); ?></span>
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue assets on edit screens.
	 *
	 * @param string $hook Admin page hook.
	 * @return void
	 */
	public function enqueue_meta_box_assets( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );

		$css_ver = file_exists( NEXURA_AI_SEARCH_DIR . 'assets/css/admin.css' ) ? filemtime( NEXURA_AI_SEARCH_DIR . 'assets/css/admin.css' ) : NEXURA_AI_SEARCH_VERSION;
		$js_ver  = file_exists( NEXURA_AI_SEARCH_DIR . 'assets/js/admin.js' ) ? filemtime( NEXURA_AI_SEARCH_DIR . 'assets/js/admin.js' ) : NEXURA_AI_SEARCH_VERSION;

		wp_enqueue_style(
			'naso-admin-css',
			NEXURA_AI_SEARCH_URL . 'assets/css/admin.css',
			array( 'dashicons' ),
			$css_ver
		);

		wp_enqueue_script(
			'naso-admin-js',
			NEXURA_AI_SEARCH_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$js_ver,
			true
		);

		$is_pro      = ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) && naso_fs()->can_use_premium_code() );
		$has_pro_ai  = ( $is_pro && class_exists( '\NexuraAISearchPro\AI_Agent' ) && \NexuraAISearchPro\AI_Agent::is_configured() );
		$upgrade_url = ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) ) ? naso_fs()->get_upgrade_url() : admin_url( 'admin.php?page=nexura-ai-search-upgrade' );

		wp_localize_script(
			'naso-admin-js',
			'nasoAdminData',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'adminPostUrl' => admin_url( 'admin-post.php' ),
				'nonce'        => wp_create_nonce( 'naso_admin_nonce' ),
				'pdfNonce'     => wp_create_nonce( 'naso_export_pdf' ),
				'auditTabUrl'  => admin_url( 'admin.php?page=nexura-ai-search-audit' ),
				'copiedMsg'    => __( 'Copied to clipboard!', 'nexura-ai-search-optimizer' ),
				'running'      => __( 'Scanning content...', 'nexura-ai-search-optimizer' ),
				'isPro'        => $is_pro ? 1 : 0,
				'hasProAi'     => $has_pro_ai ? 1 : 0,
				'proAgentUrl'  => admin_url( 'admin.php?page=nexura-ai-search-agent' ),
				'upgradeUrl'   => $upgrade_url,
			)
		);
	}

	/**
	 * AJAX endpoint to retrieve 1-Click AI Fix Prompt.
	 *
	 * @return void
	 */
	public function ajax_get_ai_fix_prompt() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Post ID or unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		$data = AI_Prompt_Fix::generate_prompt_data( $post_id );

		wp_send_json_success( $data );
	}

	/**
	 * Save Meta Box data when post is saved.
	 *
	 * @param int $post_id Post ID being saved.
	 * @return void
	 */
	public function save_meta_box_data( $post_id ) {
		// Nonce check
		if ( ! isset( $_POST['naso_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['naso_meta_box_nonce'] ) ), 'naso_meta_box_save' ) ) {
			return;
		}

		// Autosave guard
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check capability
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if user set exclusion
		if ( isset( $_POST['naso_exclude_from_ai'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['naso_exclude_from_ai'] ) ) ) {
			update_post_meta( $post_id, '_naso_exclude_from_ai', '1' );
			delete_post_meta( $post_id, '_naso_geo_score' );
			delete_post_meta( $post_id, '_naso_geo_rating' );
			delete_post_meta( $post_id, '_naso_geo_badge' );
			delete_post_meta( $post_id, '_naso_geo_recs' );
			delete_post_meta( $post_id, '_naso_geo_last_audit' );
		} else {
			delete_post_meta( $post_id, '_naso_exclude_from_ai' );
		}
	}
}
