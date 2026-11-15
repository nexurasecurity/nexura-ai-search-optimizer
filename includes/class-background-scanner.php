<?php
/**
 * Background & Auto-Scanner Engine for AI Search Optimization
 *
 * Automatically scans content upon plugin activation and provides one-click
 * batch scanning across WordPress Posts, Pages & WooCommerce Products.
 *
 * Fully optimized for low-end servers (max execution time protection, small batches,
 * non-blocking cron scheduling).
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles automatic background audits and batch operations.
 */
class Background_Scanner {

	/**
	 * Single cron hook name.
	 */
	const CRON_HOOK = 'naso_cron_background_scan';

	/**
	 * Option key indicating initial scan is needed.
	 */
	const OPTION_SCAN_NEEDED = 'naso_initial_scan_needed';

	/**
	 * Option key indicating initial scan is completed.
	 */
	const OPTION_SCAN_DONE = 'naso_initial_scan_done';

	/**
	 * Transient key to hide auto-scan banner for user session.
	 */
	const TRANSIENT_DISMISS_BANNER = 'naso_dismiss_scan_banner_';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// WP-Cron Background Worker
		add_action( self::CRON_HOOK, array( $this, 'cron_worker' ) );

		// AJAX Batch Scanning Endpoint
		add_action( 'wp_ajax_naso_scan_batch_ajax', array( $this, 'ajax_scan_batch' ) );
		add_action( 'wp_ajax_naso_dismiss_scan_notice', array( $this, 'ajax_dismiss_notice' ) );

		// Register Bulk Actions in WP Content Tables
		$post_types = Settings::get_supported_post_types();
		foreach ( $post_types as $pt ) {
			add_filter( "bulk_actions-edit-{$pt}", array( $this, 'register_bulk_action' ) );
			add_filter( "handle_bulk_actions-edit-{$pt}", array( $this, 'handle_bulk_action' ), 10, 3 );
		}

		// Admin Notices
		add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
	}

	/**
	 * Trigger initial auto-scan upon plugin activation.
	 *
	 * @return void
	 */
	public static function trigger_initial_scan() {
		$unscanned = self::get_unscanned_count();
		if ( $unscanned > 0 ) {
			update_option( self::OPTION_SCAN_NEEDED, 1 );
			if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
				wp_schedule_single_event( time() + 3, self::CRON_HOOK );
			}
		}
	}

	/**
	 * Get count of published posts/products missing a GEO score.
	 *
	 * @param string|null $post_type Optional post type filter.
	 * @return int
	 */
	public static function get_unscanned_count( $post_type = null ) {
		$post_types = $post_type ? array( $post_type ) : Settings::get_supported_post_types();
		if ( empty( $post_types ) ) {
			return 0;
		}

		$excluded_ids = LLMS_Generator::get_excluded_post_ids();

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary to detect posts without an AI audit score in background cron/admin.
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_naso_geo_score',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_naso_geo_score',
					'value'   => '',
					'compare' => '=',
				),
			),
		);

		if ( ! empty( $excluded_ids ) ) {
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Excludes specific utility pages (cart, checkout) configured by site owner.
			$args['post__not_in'] = $excluded_ids;
		}

		$query = new \WP_Query( $args );

		return absint( $query->found_posts );
	}

	/**
	 * Get IDs of published posts/products missing a GEO score.
	 *
	 * @param int         $limit Limit of IDs to fetch.
	 * @param string|null $post_type Optional post type filter.
	 * @return int[]
	 */
	public static function get_unscanned_ids( $limit = 15, $post_type = null ) {
		$post_types = $post_type ? array( $post_type ) : Settings::get_supported_post_types();
		if ( empty( $post_types ) ) {
			return array();
		}

		$excluded_ids = LLMS_Generator::get_excluded_post_ids();

		$args = array(
			'post_type'              => $post_types,
			'post_status'            => 'publish',
			'posts_per_page'         => max( 1, absint( $limit ) ),
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary to fetch unscanned items for batch background processing.
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => '_naso_geo_score',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_naso_geo_score',
					'value'   => '',
					'compare' => '=',
				),
			),
		);

		if ( ! empty( $excluded_ids ) ) {
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Excludes specific utility pages (cart, checkout) configured by site owner.
			$args['post__not_in'] = $excluded_ids;
		}

		$query = new \WP_Query( $args );

		return array_map( 'absint', (array) $query->posts );
	}

	/**
	 * WP-Cron worker that executes in the background without blocking the user.
	 *
	 * @return void
	 */
	public function cron_worker() {
		$start_time = microtime( true );
		$auditor    = new GEO_Audit();
		$batch_ids  = self::get_unscanned_ids( 15 );

		if ( empty( $batch_ids ) ) {
			delete_option( self::OPTION_SCAN_NEEDED );
			update_option( self::OPTION_SCAN_DONE, time() );
			return;
		}

		foreach ( $batch_ids as $id ) {
			// Low-end server timeout guard: do not exceed 15 seconds
			if ( ( microtime( true ) - $start_time ) > 15 ) {
				break;
			}
			if ( LLMS_Generator::is_excluded_utility_page( $id ) ) {
				continue;
			}
			$auditor->audit_post( $id );
		}

		// Check if more items remain to be scanned
		$remaining = self::get_unscanned_count();
		if ( $remaining > 0 ) {
			// Schedule next batch in 10 seconds
			if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
				wp_schedule_single_event( time() + 10, self::CRON_HOOK );
			}
		} else {
			delete_option( self::OPTION_SCAN_NEEDED );
			update_option( self::OPTION_SCAN_DONE, time() );
		}
	}

	/**
	 * AJAX batch scanning handler for interactive UI (progress bar in List Tables).
	 *
	 * @return void
	 */
	public function ajax_scan_batch() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		$post_type    = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : null;
		$specific_ids = isset( $_POST['specific_ids'] ) ? array_map( 'absint', (array) $_POST['specific_ids'] ) : array();
		$limit        = isset( $_POST['limit'] ) ? min( 15, absint( $_POST['limit'] ) ) : 5;

		if ( ! empty( $specific_ids ) ) {
			// Pick the first $limit IDs from specific_ids
			$target_ids = array_slice( $specific_ids, 0, $limit );
		} else {
			$target_ids = self::get_unscanned_ids( $limit, $post_type );
		}

		$auditor = new GEO_Audit();
		$results = array();

		foreach ( $target_ids as $p_id ) {
			if ( LLMS_Generator::is_excluded_utility_page( $p_id ) ) {
				delete_post_meta( $p_id, '_naso_geo_score' );
				delete_post_meta( $p_id, '_naso_geo_rating' );
				delete_post_meta( $p_id, '_naso_geo_badge' );
				delete_post_meta( $p_id, '_naso_geo_recs' );
				delete_post_meta( $p_id, '_naso_geo_last_audit' );

				ob_start();
				?>
				<div style="display:inline-flex; align-items:center; gap:6px;">
					<span class="naso-badge naso-badge-utility" style="background:#f0f0f1; color:#50575e; border:1px solid #dcdcde; font-weight:600; font-size:11px; padding:3px 8px; border-radius:4px;" title="<?php esc_attr_e( 'System and transactional utility pages (Cart, Checkout, Account, etc.) are excluded from AI search crawling and /llms.txt.', 'nexura-ai-search-optimizer' ); ?>">
						<?php esc_html_e( 'System Page', 'nexura-ai-search-optimizer' ); ?>
					</span>
				</div>
				<div style="font-size:11px; color:#8c8f94; margin-top:3px;"><?php esc_html_e( 'Excluded from SEO', 'nexura-ai-search-optimizer' ); ?></div>
				<div class="row-actions visible" style="margin-top: 4px; font-size: 11px;">
					<span class="description" style="color:#8c8f94;"><?php esc_html_e( 'Utility (No SEO Needed)', 'nexura-ai-search-optimizer' ); ?></span>
				</div>
				<?php
				$cell_html = ob_get_clean();

				$results[] = array(
					'id'          => $p_id,
					'score'       => null,
					'rating'      => __( 'System Page', 'nexura-ai-search-optimizer' ),
					'badge_class' => 'naso-badge-utility',
					'cell_html'   => $cell_html,
				);
				continue;
			}
			$res = $auditor->audit_post( $p_id );

			$score       = isset( $res['score'] ) ? absint( $res['score'] ) : 0;
			$badge_class = isset( $res['badge_class'] ) ? $res['badge_class'] : 'naso-badge-warning';
			$rating      = isset( $res['rating'] ) ? $res['rating'] : '';

			// Build rendered cell HTML
			$audit_url = admin_url( 'admin.php?page=nexura-ai-search-audit&post_id=' . $p_id );
			$pdf_url   = wp_nonce_url( admin_url( 'admin-post.php?action=naso_export_pdf&post_id=' . $p_id ), 'naso_export_pdf', 'nonce' );

			ob_start();
			?>
			<div style="display:inline-flex; align-items:center; gap:6px;">
				<span class="naso-badge <?php echo esc_attr( $badge_class ); ?>" style="font-weight:700; font-size:12px; padding:3px 8px;">
					<?php echo esc_html( $score ); ?> / 100
				</span>
			</div>
			<?php if ( ! empty( $rating ) ) : ?>
				<div style="font-size:11px; color:#50575e; margin-top:2px;"><?php echo esc_html( $rating ); ?></div>
			<?php endif; ?>
			<div class="row-actions visible" style="margin-top: 4px; font-size: 11px;">
				<span class="naso-act-audit"><a href="<?php echo esc_url( $audit_url ); ?>" title="<?php esc_attr_e( 'Run Full GEO Content Audit', 'nexura-ai-search-optimizer' ); ?>"><?php esc_html_e( 'Scan', 'nexura-ai-search-optimizer' ); ?></a> | </span>
				<?php if ( $score < 100 ) : ?>
					<span class="naso-act-prompt"><a href="#" class="naso-trigger-ai-prompt" data-id="<?php echo esc_attr( $p_id ); ?>" style="color:#2271b1; font-weight:600;" title="<?php esc_attr_e( '1-Click AI Fix Prompt for ChatGPT / Claude', 'nexura-ai-search-optimizer' ); ?>">🤖 <?php esc_html_e( 'AI Fix', 'nexura-ai-search-optimizer' ); ?></a> | </span>
				<?php endif; ?>
				<span class="naso-act-pdf"><a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" title="<?php esc_attr_e( 'Printable PDF Report', 'nexura-ai-search-optimizer' ); ?>">📄 <?php esc_html_e( 'PDF', 'nexura-ai-search-optimizer' ); ?></a></span>
			</div>
			<?php
			$cell_html = ob_get_clean();

			$results[] = array(
				'id'          => $p_id,
				'score'       => $score,
				'rating'      => $rating,
				'badge_class' => $badge_class,
				'cell_html'   => $cell_html,
			);
		}

		$remaining = self::get_unscanned_count( $post_type );

		wp_send_json_success( array(
			'processed'       => count( $results ),
			'results'         => $results,
			'remaining_total' => $remaining,
			'is_complete'     => 0 === $remaining,
		) );
	}

	/**
	 * Dismiss auto-scan admin banner for current user.
	 *
	 * @return void
	 */
	public function ajax_dismiss_notice() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );
		$user_id = get_current_user_id();
		set_transient( self::TRANSIENT_DISMISS_BANNER . $user_id, 1, DAY_IN_SECONDS );
		wp_send_json_success();
	}

	/**
	 * Register bulk action in WP List Tables.
	 *
	 * @param array $bulk_actions Existing bulk actions.
	 * @return array
	 */
	public function register_bulk_action( $bulk_actions ) {
		$bulk_actions['naso_bulk_geo_scan'] = __( '⚡ Run AI GEO Scan', 'nexura-ai-search-optimizer' );
		return $bulk_actions;
	}

	/**
	 * Handle execution of the bulk action.
	 *
	 * @param string $redirect_to The redirect URL.
	 * @param string $doaction The action being taken.
	 * @param array  $post_ids Array of post IDs.
	 * @return string
	 */
	public function handle_bulk_action( $redirect_to, $doaction, $post_ids ) {
		if ( 'naso_bulk_geo_scan' !== $doaction || empty( $post_ids ) ) {
			return $redirect_to;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return $redirect_to;
		}

		$auditor = new GEO_Audit();
		$scanned = 0;

		foreach ( $post_ids as $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}
			$auditor->audit_post( $post_id );
			$scanned++;
		}

		return add_query_arg( 'naso_bulk_scanned', $scanned, $redirect_to );
	}

	/**
	 * Render admin notices for bulk scan results and initial background scanner.
	 *
	 * @return void
	 */
	public function render_admin_notices() {
		// 1. Notice after bulk action
		if ( isset( $_GET['naso_bulk_scanned'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = absint( $_GET['naso_bulk_scanned'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong>⚡ <?php esc_html_e( 'Nexura AI Search:', 'nexura-ai-search-optimizer' ); ?></strong>
					<?php
					/* translators: %d: number of scanned items */
					echo esc_html( sprintf( __( 'Successfully scanned %d items for AI search readiness (ChatGPT, Claude, Perplexity)!', 'nexura-ai-search-optimizer' ), absint( $count ) ) );
					?>
				</p>
			</div>
			<?php
		}

		// 2. Global notice if initial scan is needed or in progress
		$screen = get_current_screen();
		if ( ! $screen || 'post' === $screen->base ) {
			return; // Don't show banner inside block editor or classic editor screen
		}

		$user_id = get_current_user_id();
		if ( get_transient( self::TRANSIENT_DISMISS_BANNER . $user_id ) ) {
			return;
		}

		$unscanned = self::get_unscanned_count();
		if ( $unscanned <= 0 ) {
			return;
		}

		$needs_scan = get_option( self::OPTION_SCAN_NEEDED, false );
		?>
		<div class="notice notice-info naso-auto-scan-notice" style="border-left-color: #2271b1; padding: 12px 16px; position:relative;">
			<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
				<div style="display:flex; align-items:center; gap:10px;">
					<span class="dashicons dashicons-superhero-alt" style="font-size:24px; width:24px; height:24px; color:#2271b1;"></span>
					<div>
						<strong style="font-size:14px; color:#1d2327;">
							<?php esc_html_e( 'Nexura AI Search: Content Optimization Needed', 'nexura-ai-search-optimizer' ); ?>
						</strong>
						<p style="margin:2px 0 0; color:#50575e; font-size:13px;">
							<?php
							/* translators: %d: number of unscanned items */
							echo esc_html( sprintf( __( 'You have %d published items not yet analyzed for AI Search Engines (ChatGPT, Perplexity, Google AI Overviews).', 'nexura-ai-search-optimizer' ), absint( $unscanned ) ) );
							?>
						</p>
					</div>
				</div>
				<div style="display:flex; align-items:center; gap:8px;">
					<button type="button" class="button button-primary naso-btn-scan-all-global" data-total="<?php echo esc_attr( $unscanned ); ?>" style="display:inline-flex; align-items:center; gap:5px; height:32px; font-weight:600;">
						<span class="dashicons dashicons-update" style="font-size:16px; width:16px; height:16px; margin:0;"></span>
						<?php
						/* translators: %d: number of items */
						echo esc_html( sprintf( __( '⚡ Scan All %d Items Now', 'nexura-ai-search-optimizer' ), absint( $unscanned ) ) );
						?>
					</button>
					<button type="button" class="button naso-btn-dismiss-scan-notice" style="height:32px;">
						<?php esc_html_e( 'Dismiss', 'nexura-ai-search-optimizer' ); ?>
					</button>
				</div>
			</div>
			<!-- Progress Bar (Hidden initially) -->
			<div class="naso-global-scan-progress" style="display:none; margin-top:10px; padding-top:10px; border-top:1px solid #dcdcde;">
				<div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; font-weight:600;">
					<span class="naso-scan-status-text"><?php esc_html_e( 'Starting AI search audit...', 'nexura-ai-search-optimizer' ); ?></span>
					<span class="naso-scan-percent-text">0%</span>
				</div>
				<div style="background:#e0e0e0; border-radius:4px; height:10px; overflow:hidden;">
					<div class="naso-progress-fill" style="background:#2271b1; height:100%; width:0%; transition:width 0.3s ease;"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
