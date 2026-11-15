<?php
/**
 * Admin List Table Columns for Posts, Pages & WooCommerce Products
 *
 * Adds GEO Score and AI Search Readiness indicators directly into WordPress
 * admin content tables with quick actions (Audit, AI Fix, PDF Export).
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles custom columns in WP List Tables.
 */
class List_Table_Columns {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// All supported post types (Posts, Pages, WooCommerce Products, etc.)
		$post_types = Settings::get_supported_post_types();
		foreach ( $post_types as $pt ) {
			if ( 'product' === $pt ) {
				add_filter( 'manage_edit-product_columns', array( $this, 'add_column_header' ), 20 );
				add_action( 'manage_product_posts_custom_column', array( $this, 'render_column_content' ), 10, 2 );
			} else {
				add_filter( "manage_{$pt}_posts_columns", array( $this, 'add_column_header' ) );
				add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_column_content' ), 10, 2 );
			}
		}

		// Action button above list table (top table nav)
		add_action( 'manage_posts_extra_tablenav', array( $this, 'render_table_nav_button' ) );

		// Enqueue scripts & styles on edit.php screens
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_table_assets' ) );
	}

	/**
	 * Add AI Search column header.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_column_header( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			// Insert right before Date or at the end
			if ( 'date' === $key ) {
				$new_columns['naso_geo_score'] = '<span class="dashicons dashicons-superhero-alt" style="color:#2271b1;vertical-align:text-bottom;font-size:16px;"></span> ' . __( 'AI Search (GEO)', 'nexura-ai-search-optimizer' );
			}
		}

		if ( ! isset( $new_columns['naso_geo_score'] ) ) {
			$new_columns['naso_geo_score'] = '<span class="dashicons dashicons-superhero-alt" style="color:#2271b1;vertical-align:text-bottom;font-size:16px;"></span> ' . __( 'AI Search (GEO)', 'nexura-ai-search-optimizer' );
		}

		return $new_columns;
	}

	/**
	 * Render "Scan All Unscanned" action button in table navigation.
	 *
	 * @param string $which Table navigation position ('top' or 'bottom').
	 * @return void
	 */
	public function render_table_nav_button( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'edit' !== $screen->base ) {
			return;
		}

		$post_type = $screen->post_type;
		$supported = Settings::get_supported_post_types();
		if ( ! in_array( $post_type, $supported, true ) ) {
			return;
		}

		$unscanned = Background_Scanner::get_unscanned_count( $post_type );
		if ( $unscanned <= 0 ) {
			return;
		}

		?>
		<div class="alignleft actions naso-tablenav-scan-box" style="display:inline-flex; align-items:center; gap:6px; margin-right:8px;">
			<button type="button" class="button button-secondary naso-btn-scan-all-table" data-post-type="<?php echo esc_attr( $post_type ); ?>" data-total="<?php echo esc_attr( $unscanned ); ?>" style="border-color:#2271b1; color:#2271b1; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
				<span class="dashicons dashicons-superhero-alt" style="font-size:16px; width:16px; height:16px; line-height:16px;"></span>
				<?php
				/* translators: %d: unscanned count */
				echo esc_html( sprintf( __( '⚡ Scan Unscanned (%d)', 'nexura-ai-search-optimizer' ), absint( $unscanned ) ) );
				?>
			</button>
			<span class="naso-table-scan-progress" style="font-size:12px; color:#50575e; display:none; font-weight:600;"></span>
		</div>
		<?php
	}

	/**
	 * Render column content for a post/product.
	 *
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column_content( $column, $post_id ) {
		if ( 'naso_geo_score' !== $column ) {
			return;
		}

		// System utility pages or User-excluded pages are excluded from AI search.
		if ( \NexuraAISearch\LLMS_Generator::is_excluded_utility_page( $post_id ) ) {
			$is_system_util   = \NexuraAISearch\LLMS_Generator::is_system_utility_page( $post_id );
			$is_user_excluded = ! $is_system_util;

			$badge_label = $is_user_excluded ? __( 'Excluded from AI', 'nexura-ai-search-optimizer' ) : ( $is_system_util ? __( 'System Page', 'nexura-ai-search-optimizer' ) : __( 'Excluded', 'nexura-ai-search-optimizer' ) );
			$sub_label   = $is_user_excluded ? __( 'Custom Excluded from AI', 'nexura-ai-search-optimizer' ) : __( 'Excluded from SEO', 'nexura-ai-search-optimizer' );
			$action_desc = $is_user_excluded ? __( 'Hidden from AI Engines', 'nexura-ai-search-optimizer' ) : __( 'Utility (No SEO Needed)', 'nexura-ai-search-optimizer' );

			echo '<div class="naso-table-cell naso-cell-utility" data-post-id="' . esc_attr( $post_id ) . '">';
			echo '<div style="display:inline-flex; align-items:center; gap:6px;">';
			echo '<span class="naso-badge naso-badge-utility" style="background:#f0f0f1; color:#50575e; border:1px solid #dcdcde; font-weight:600; font-size:11px; padding:3px 8px; border-radius:4px;" title="' . esc_attr__( 'This page is excluded from AI search crawling and /llms.txt.', 'nexura-ai-search-optimizer' ) . '">';
			echo esc_html( $badge_label );
			echo '</span>';
			echo '</div>';
			echo '<div style="font-size:11px; color:#8c8f94; margin-top:3px;">' . esc_html( $sub_label ) . '</div>';
			echo '<div class="row-actions visible" style="margin-top: 4px; font-size: 11px;">';
			echo '<span class="description" style="color:#8c8f94;">' . esc_html( $action_desc ) . '</span>';
			echo '</div>';
			echo '</div>';
			return;
		}

		$score       = get_post_meta( $post_id, '_naso_geo_score', true );
		$badge_class = get_post_meta( $post_id, '_naso_geo_badge', true );
		$rating      = get_post_meta( $post_id, '_naso_geo_rating', true );
		$has_scanned = ( '' !== $score && false !== $score );
		$score_int   = $has_scanned ? absint( $score ) : -1;

		$audit_url = admin_url( 'admin.php?page=nexura-ai-search-audit&post_id=' . $post_id );
		$pdf_url   = wp_nonce_url( admin_url( 'admin-post.php?action=naso_export_pdf&post_id=' . $post_id ), 'naso_export_pdf', 'nonce' );

		echo '<div class="naso-table-cell" data-post-id="' . esc_attr( $post_id ) . '">';

		if ( $has_scanned ) {
			if ( empty( $badge_class ) ) {
				$badge_class = ( $score_int >= 80 ) ? 'naso-badge-success' : ( ( $score_int >= 50 ) ? 'naso-badge-warning' : 'naso-badge-danger' );
			}

			echo '<div style="display:inline-flex; align-items:center; gap:6px;">';
			echo '<span class="naso-badge ' . esc_attr( $badge_class ) . '" style="font-weight:700; font-size:12px; padding:3px 8px;">';
			echo esc_html( $score_int ) . ' / 100';
			echo '</span>';
			echo '</div>';

			if ( ! empty( $rating ) ) {
				echo '<div style="font-size:11px; color:#50575e; margin-top:2px;">' . esc_html( $rating ) . '</div>';
			}
		} else {
			echo '<span class="naso-badge" style="background:#f0f0f1; color:#646970; border:1px solid #dcdcde;">' . esc_html__( 'Not Scanned', 'nexura-ai-search-optimizer' ) . '</span>';
		}

		// Quick action links
		echo '<div class="row-actions visible" style="margin-top: 4px; font-size: 11px;">';
		echo '<span class="naso-act-audit"><a href="#" class="naso-trigger-row-scan" data-id="' . esc_attr( $post_id ) . '" title="' . esc_attr__( 'Quick In-Place AI Scan', 'nexura-ai-search-optimizer' ) . '">' . esc_html__( 'Scan', 'nexura-ai-search-optimizer' ) . '</a> | </span>';
		if ( $has_scanned && $score_int < 100 ) {
			echo '<span class="naso-act-prompt"><a href="#" class="naso-trigger-ai-prompt" data-id="' . esc_attr( $post_id ) . '" style="color:#2271b1; font-weight:600;" title="' . esc_attr__( '1-Click AI Fix Prompt for ChatGPT / Claude', 'nexura-ai-search-optimizer' ) . '">🤖 ' . esc_html__( 'AI Fix', 'nexura-ai-search-optimizer' ) . '</a> | </span>';
		}
		echo '<span class="naso-act-pdf"><a href="' . esc_url( $pdf_url ) . '" target="_blank" title="' . esc_attr__( 'Printable PDF Report', 'nexura-ai-search-optimizer' ) . '">📄 ' . esc_html__( 'PDF', 'nexura-ai-search-optimizer' ) . '</a></span>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Enqueue styles and scripts on edit.php.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_table_assets( $hook ) {
		if ( 'edit.php' !== $hook ) {
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

		$is_pro = ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) && naso_fs()->can_use_premium_code() );
		$has_pro_ai = ( $is_pro && class_exists( '\NexuraAISearchPro\AI_Agent' ) && \NexuraAISearchPro\AI_Agent::is_configured() );
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
}
