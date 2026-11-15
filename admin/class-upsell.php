<?php
/**
 * Upsell and Freemius Integration Controller
 *
 * @package NexuraAISearch\Admin
 */

namespace NexuraAISearch\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WordPress.org compliant upsells, feature highlights, and dismissible notices.
 */
class Upsell {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_naso_dismiss_upsell', array( $this, 'ajax_dismiss_notice' ) );
	}

	/**
	 * Get Freemius checkout / upgrade URL safely.
	 *
	 * @return string
	 */
	public static function get_upgrade_url() {
		if ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) ) {
			return naso_fs()->get_upgrade_url();
		}
		return admin_url( 'admin.php?page=nexura-ai-search-upgrade' );
	}

	/**
	 * Get direct checkout URL.
	 *
	 * @param string $plan_id Target plan identifier.
	 * @return string
	 */
	public static function get_checkout_url( $plan_id = 'annual_single' ) {
		if ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) ) {
			return naso_fs()->get_upgrade_url();
		}
		return admin_url( 'admin.php?page=nexura-ai-search-upgrade' );
	}

	/**
	 * Check if current installation has Pro license active.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		if ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) ) {
			return naso_fs()->can_use_premium_code();
		}
		return false;
	}

	/**
	 * Render a dismissible Pro enhancement banner.
	 *
	 * @return void
	 */
	public function render_dashboard_banner() {
		if ( self::is_pro() ) {
			return;
		}

		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'naso_pro_notice_dismissed', true );
		if ( $dismissed ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible naso-upsell-banner" id="naso-upsell-notice">
			<p>
				<strong><?php esc_html_e( 'Level Up Your Generative Engine Optimization with Nexura Pro:', 'nexura-ai-search-optimizer' ); ?></strong>
				<?php esc_html_e( 'Unlock 1-Click Bulk GEO Scanning, Automated Weekly Audits, WooCommerce Product Optimization, and Real-time AI Crawler Analytics.', 'nexura-ai-search-optimizer' ); ?>
				<a href="<?php echo esc_url( self::get_upgrade_url() ); ?>" class="button button-primary" style="margin-left: 10px;">
					<?php esc_html_e( 'View Pro Features & Pricing &rarr;', 'nexura-ai-search-optimizer' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX handler for dismissing notice.
	 *
	 * @return void
	 */
	public function ajax_dismiss_notice() {
		check_ajax_referer( 'naso_admin_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( $user_id ) {
			update_user_meta( $user_id, 'naso_pro_notice_dismissed', 1 );
			wp_send_json_success();
		}

		wp_send_json_error();
	}
}
