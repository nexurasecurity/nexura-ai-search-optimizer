<?php
/**
 * Admin Controller
 *
 * @package NexuraAISearch\Admin
 */

namespace NexuraAISearch\Admin;

use NexuraAISearch\Settings;
use NexuraAISearch\Cache;
use NexuraAISearch\GEO_Audit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WordPress admin menus, scripts, styles, and AJAX endpoints.
 */
class Admin {

	/**
	 * Onboarding wizard instance.
	 *
	 * @var Onboarding
	 */
	public $onboarding;

	/**
	 * Upsell manager instance.
	 *
	 * @var Upsell
	 */
	public $upsell;

	/**
	 * PDF exporter instance.
	 *
	 * @var \NexuraAISearch\Export_PDF
	 */
	public $export_pdf;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->onboarding = new Onboarding();
		$this->upsell     = new Upsell();
		$this->export_pdf = new \NexuraAISearch\Export_PDF();
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'maybe_redirect_legacy_tabs' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( NEXURA_AI_SEARCH_FILE ), array( $this, 'add_action_links' ) );

		// AJAX Endpoints
		add_action( 'wp_ajax_naso_run_audit', array( $this, 'ajax_run_audit' ) );
		add_action( 'wp_ajax_naso_rebuild_cache', array( $this, 'ajax_rebuild_cache' ) );
		add_action( 'wp_ajax_naso_search_posts', array( $this, 'ajax_search_posts' ) );
		add_action( 'wp_ajax_naso_save_onboarding', array( $this->onboarding, 'ajax_save_step' ) );

		$this->onboarding->register_hooks();
		$this->upsell->register_hooks();
		$this->export_pdf->register_hooks();
	}

	/**
	 * Redirect legacy or separate menu page slugs to the main unified tabbed screen.
	 * Prevents "Sorry, you are not allowed to access this page" (403) errors.
	 *
	 * @return void
	 */
	public function maybe_redirect_legacy_tabs() {
		// Handled natively by submenu page registration
	}

	/**
	 * Add menu pages to WordPress admin sidebar.
	 *
	 * @return void
	 */
	public function add_menu_pages() {
		$main_slug = 'nexura-ai-search';

		// Top Level Menu
		add_menu_page(
			__( 'Nexura AI Search Optimizer', 'nexura-ai-search-optimizer' ),
			__( 'AI Search Optimizer', 'nexura-ai-search-optimizer' ),
			'manage_options',
			$main_slug,
			array( $this, 'render_main_page' ),
			'dashicons-superhero-alt',
			81
		);

		// Submenus linked cleanly to respective tabs
		add_submenu_page(
			$main_slug,
			__( 'Dashboard & Health', 'nexura-ai-search-optimizer' ),
			__( 'Dashboard', 'nexura-ai-search-optimizer' ),
			'manage_options',
			$main_slug,
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			$main_slug,
			__( 'LLMS.txt Configuration', 'nexura-ai-search-optimizer' ),
			__( 'LLMS.txt', 'nexura-ai-search-optimizer' ),
			'manage_options',
			'nexura-ai-search-llms',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			$main_slug,
			__( 'AI Crawlers & Robots.txt', 'nexura-ai-search-optimizer' ),
			__( 'AI Bot Access', 'nexura-ai-search-optimizer' ),
			'manage_options',
			'nexura-ai-search-crawlers',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			$main_slug,
			__( 'GEO Content Audit', 'nexura-ai-search-optimizer' ),
			__( 'GEO Audit', 'nexura-ai-search-optimizer' ),
			'manage_options',
			'nexura-ai-search-audit',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			$main_slug,
			__( 'Advanced Settings & Cache', 'nexura-ai-search-optimizer' ),
			__( 'Advanced', 'nexura-ai-search-optimizer' ),
			'manage_options',
			'nexura-ai-search-advanced',
			array( $this, 'render_main_page' )
		);

		// Pro Submenu (Only shown if Pro is not active)
		if ( ! function_exists( 'naso_fs' ) || ! naso_fs()->can_use_premium_code() ) {
			add_submenu_page(
				$main_slug,
				__( 'Upgrade to Nexura Pro', 'nexura-ai-search-optimizer' ),
				'<span style="color:#2271b1;font-weight:600;">' . __( 'Upgrade to Pro ★', 'nexura-ai-search-optimizer' ) . '</span>',
				'manage_options',
				'nexura-ai-search-upgrade',
				array( $this, 'render_upgrade_page' )
			);
		}
	}

	/**
	 * Enqueue admin CSS and JS.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		// Only enqueue on Nexura admin pages or onboarding wizard
		if ( strpos( $hook_suffix, 'nexura-ai' ) === false ) {
			return;
		}

		// Ensure WordPress core Dashicons stylesheet is always loaded
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

	/**
	 * Add custom action links in plugins list.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$custom_links = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=nexura-ai-search' ) ) . '">' . esc_html__( 'Dashboard', 'nexura-ai-search-optimizer' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=nexura-ai-search-audit' ) ) . '">' . esc_html__( 'GEO Audit', 'nexura-ai-search-optimizer' ) . '</a>',
		);

		if ( ! function_exists( 'naso_fs' ) || ! naso_fs()->can_use_premium_code() ) {
			$upgrade_url = function_exists( 'naso_fs' ) ? naso_fs()->get_upgrade_url() : admin_url( 'admin.php?page=nexura-ai-search-upgrade' );
			$custom_links[] = '<a href="' . esc_url( $upgrade_url ) . '" style="color:#d63638;font-weight:600;">' . esc_html__( 'Go Pro', 'nexura-ai-search-optimizer' ) . '</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * Render main plugin admin page with tabs.
	 *
	 * @return void
	 */
	public function render_main_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Query parameter used for view routing only.
		$current_tab = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'nexura-ai-search';
		require_once NEXURA_AI_SEARCH_DIR . 'admin/views/settings-view.php';
	}

	/**
	 * Render upgrade page.
	 *
	 * @return void
	 */
	public function render_upgrade_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		require_once NEXURA_AI_SEARCH_DIR . 'admin/views/upgrade-view.php';
	}

	/**
	 * AJAX handler for running single on-page GEO audit.
	 *
	 * @return void
	 */
	public function ajax_run_audit() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Post ID specified.', 'nexura-ai-search-optimizer' ) ) );
		}

		$auditor = new GEO_Audit();
		$result  = $auditor->audit_post( $post_id );

		wp_send_json_success( $result );
	}

	/**
	 * AJAX handler for rebuilding / flushing LLMS cache.
	 *
	 * @return void
	 */
	public function ajax_rebuild_cache() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		Cache::flush_all();
		flush_rewrite_rules();

		wp_send_json_success( array( 'message' => __( 'LLMS.txt cache rebuilt and rewrite rules flushed successfully!', 'nexura-ai-search-optimizer' ) ) );
	}

	/**
	 * AJAX endpoint to search posts/pages by title for exclusion or selection.
	 *
	 * @return void
	 */
	public function ajax_search_posts() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		$query = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
		if ( empty( $query ) ) {
			wp_send_json_success( array( 'results' => array() ) );
		}

		$post_types = Settings::get_supported_post_types();
		$posts      = get_posts( array(
			's'              => $query,
			'post_type'      => $post_types,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 15,
			'no_found_rows'  => true,
		) );

		$results = array();
		foreach ( $posts as $p ) {
			$results[] = array(
				'id'    => $p->ID,
				'title' => get_the_title( $p ),
				'type'  => $p->post_type,
			);
		}

		wp_send_json_success( array( 'results' => $results ) );
	}
}
