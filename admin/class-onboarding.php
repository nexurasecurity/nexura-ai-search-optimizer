<?php
/**
 * Onboarding Wizard Controller
 *
 * @package NexuraAISearch\Admin
 */

namespace NexuraAISearch\Admin;

use NexuraAISearch\Settings;
use NexuraAISearch\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the seamless 5-step onboarding wizard for beginner users.
 */
class Onboarding {

	/**
	 * Register onboarding hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
		add_action( 'admin_menu', array( $this, 'register_wizard_page' ) );
		add_action( 'admin_post_naso_express_setup', array( $this, 'handle_express_setup' ) );
		add_action( 'admin_post_naso_save_wizard_step', array( $this, 'handle_step_submit' ) );
		add_action( 'admin_post_naso_skip_onboarding', array( $this, 'handle_skip_onboarding' ) );
	}

	/**
	 * Check for activation transient and redirect to wizard.
	 *
	 * @return void
	 */
	public function maybe_redirect() {
		if ( ! get_transient( 'naso_activation_redirect' ) ) {
			return;
		}

		// If Freemius needs user opt-in / connect, allow Freemius to display its activation screen first.
		if ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) && naso_fs()->is_activation_mode() ) {
			return;
		}

		delete_transient( 'naso_activation_redirect' );

		if ( wp_doing_ajax() || wp_doing_cron() || is_network_admin() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=nexura-ai-onboarding' ) );
		exit;
	}

	/**
	 * Register hidden onboarding page in admin.
	 *
	 * @return void
	 */
	public function register_wizard_page() {
		add_submenu_page(
			null, // Hidden from menu sidebar
			__( 'Nexura AI Setup Wizard', 'nexura-ai-search-optimizer' ),
			__( 'Setup Wizard', 'nexura-ai-search-optimizer' ),
			'manage_options',
			'nexura-ai-onboarding',
			array( $this, 'render_wizard' )
		);
	}

	/**
	 * Render the 5-step onboarding wizard view.
	 *
	 * @return void
	 */
	public function render_wizard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		require_once NEXURA_AI_SEARCH_DIR . 'admin/views/onboarding-view.php';
	}

	/**
	 * Handle 1-Click Express Setup.
	 *
	 * @return void
	 */
	public function handle_express_setup() {
		check_admin_referer( 'naso_onboarding_action', 'naso_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		// Apply optimal recommended defaults
		$defaults = Settings::get_defaults();
		$defaults['onboarding_completed'] = 1;
		Settings::update_all( $defaults );

		Cache::flush_all();
		flush_rewrite_rules();

		wp_safe_redirect( admin_url( 'admin.php?page=nexura-ai-search&naso_msg=configured' ) );
		exit;
	}

	/**
	 * Handle Skip Wizard & Configure Manually.
	 *
	 * @return void
	 */
	public function handle_skip_onboarding() {
		check_admin_referer( 'naso_skip_wizard' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		// Mark onboarding completed so user is not redirected again
		$settings = Settings::get_all();
		$settings['onboarding_completed'] = 1;
		Settings::update_all( $settings );

		delete_transient( 'naso_activation_redirect' );
		flush_rewrite_rules();

		wp_safe_redirect( admin_url( 'admin.php?page=nexura-ai-search&naso_msg=manual' ) );
		exit;
	}

	/**
	 * Handle standard step form submissions.
	 *
	 * @return void
	 */
	public function handle_step_submit() {
		check_admin_referer( 'naso_onboarding_action', 'naso_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) );
		}

		$step = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
		$next = $step + 1;

		$settings = Settings::get_all();

		if ( 2 === $step ) {
			$settings['llms_enabled']      = ! empty( $_POST['llms_enabled'] ) ? 1 : 0;
			$settings['llms_full_enabled'] = ! empty( $_POST['llms_full_enabled'] ) ? 1 : 0;
			if ( ! empty( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ) {
				$settings['post_types'] = array_map( 'sanitize_key', $_POST['post_types'] );
			}
		} elseif ( 3 === $step ) {
			$settings['allow_oai_searchbot']    = ! empty( $_POST['allow_oai_searchbot'] ) ? 1 : 0;
			$settings['allow_claude_searchbot'] = ! empty( $_POST['allow_claude_searchbot'] ) ? 1 : 0;
			$settings['allow_perplexity']       = ! empty( $_POST['allow_perplexity'] ) ? 1 : 0;
			$settings['block_bytespider']       = ! empty( $_POST['block_bytespider'] ) ? 1 : 0;
			$settings['block_gptbot']           = ! empty( $_POST['block_gptbot'] ) ? 1 : 0;
		} elseif ( 4 === $step ) {
			$settings['markdown_output_enabled'] = ! empty( $_POST['markdown_output_enabled'] ) ? 1 : 0;
			$settings['geo_snippet_controls']    = ! empty( $_POST['geo_snippet_controls'] ) ? 1 : 0;
		} elseif ( 5 === $step ) {
			$settings['onboarding_completed'] = 1;
			Settings::update_all( $settings );
			Cache::flush_all();
			flush_rewrite_rules();

			wp_safe_redirect( admin_url( 'admin.php?page=nexura-ai-search&naso_msg=welcome' ) );
			exit;
		}

		Settings::update_all( $settings );
		wp_safe_redirect( admin_url( 'admin.php?page=nexura-ai-onboarding&step=' . $next ) );
		exit;
	}

	/**
	 * AJAX handler for live step saving.
	 *
	 * @return void
	 */
	public function ajax_save_step() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'nexura-ai-search-optimizer' ) ) );
		}

		$step = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
		$data = ( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) ? map_deep( wp_unslash( $_POST['data'] ), 'sanitize_text_field' ) : array();

		$current = Settings::get_all();
		foreach ( $data as $k => $v ) {
			if ( array_key_exists( $k, $current ) ) {
				$current[ $k ] = sanitize_text_field( $v );
			}
		}

		if ( 5 === $step ) {
			$current['onboarding_completed'] = 1;
		}

		Settings::update_all( $current );
		Cache::flush_all();

		wp_send_json_success( array( 'step' => $step ) );
	}
}
