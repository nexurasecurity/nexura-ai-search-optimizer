<?php
/**
 * Main Settings Container View
 *
 * @package NexuraAISearch\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Query parameters used for display tabs and notices only.
$naso_tab_param  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Query parameters used for display tabs and notices only.
$naso_page_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'nexura-ai-search';

$naso_tabs = apply_filters(
	'naso_admin_tabs',
	array(
		'dashboard' => array( 'title' => __( 'Dashboard', 'nexura-ai-search-optimizer' ), 'icon' => 'dashicons-dashboard', 'url' => admin_url( 'admin.php?page=nexura-ai-search' ) ),
		'llms-txt'  => array( 'title' => __( 'LLMS.txt', 'nexura-ai-search-optimizer' ), 'icon' => 'dashicons-media-text', 'url' => admin_url( 'admin.php?page=nexura-ai-search-llms' ) ),
		'crawlers'  => array( 'title' => __( 'AI Bot Access', 'nexura-ai-search-optimizer' ), 'icon' => 'dashicons-shield', 'url' => admin_url( 'admin.php?page=nexura-ai-search-crawlers' ) ),
		'audit'     => array( 'title' => __( 'GEO Audit', 'nexura-ai-search-optimizer' ), 'icon' => 'dashicons-chart-pie', 'url' => admin_url( 'admin.php?page=nexura-ai-search-audit' ) ),
		'advanced'  => array( 'title' => __( 'Advanced', 'nexura-ai-search-optimizer' ), 'icon' => 'dashicons-admin-generic', 'url' => admin_url( 'admin.php?page=nexura-ai-search-advanced' ) ),
	)
);

$naso_page_to_tab_map = apply_filters(
	'naso_page_to_tab_map',
	array(
		'nexura-ai-search'          => 'dashboard',
		'nexura-ai-search-llms'     => 'llms-txt',
		'nexura-ai-search-crawlers' => 'crawlers',
		'nexura-ai-search-audit'    => 'audit',
		'nexura-ai-search-advanced' => 'advanced',
	)
);

if ( ! empty( $naso_tab_param ) && isset( $naso_tabs[ $naso_tab_param ] ) ) {
	$naso_current_tab = $naso_tab_param;
} elseif ( isset( $naso_page_to_tab_map[ $naso_page_param ] ) ) {
	$naso_current_tab = $naso_page_to_tab_map[ $naso_page_param ];
} else {
	$naso_current_tab = 'dashboard';
}

$naso_current_tab = apply_filters( 'naso_admin_current_tab', $naso_current_tab, $naso_page_param, $naso_tab_param );
?>

<div class="wrap naso-admin-wrap">
	<header class="naso-admin-header">
		<div class="naso-header-title">
			<span class="dashicons dashicons-superhero-alt naso-brand-icon"></span>
			<div>
				<h1><?php esc_html_e( 'Nexura AI Search Optimizer', 'nexura-ai-search-optimizer' ); ?></h1>
				<p class="naso-subtitle"><?php esc_html_e( 'Generative Engine Optimization (GEO) & Machine-Readable Content Infrastructure for 2026', 'nexura-ai-search-optimizer' ); ?></p>
			</div>
		</div>
		<div class="naso-header-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=nexura-ai-onboarding' ) ); ?>" class="naso-btn-secondary">
				<span class="dashicons dashicons-controls-repeat"></span>
				<span><?php esc_html_e( 'Setup Wizard', 'nexura-ai-search-optimizer' ); ?></span>
			</a>
			<?php if ( ! \NexuraAISearch\Admin\Upsell::is_pro() ) : ?>
				<a href="<?php echo esc_url( \NexuraAISearch\Admin\Upsell::get_upgrade_url() ); ?>" class="naso-btn-primary naso-pro-btn">
					<?php esc_html_e( 'Upgrade to Pro ★', 'nexura-ai-search-optimizer' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</header>

	<?php
	// Success messages
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['naso_msg'] ) && 'configured' === $_GET['naso_msg'] ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Success!', 'nexura-ai-search-optimizer' ) . '</strong> ' . esc_html__( 'Nexura Express Setup completed successfully. Your AI search endpoints are live!', 'nexura-ai-search-optimizer' ) . '</p></div>';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['naso_msg'] ) && 'manual' === $_GET['naso_msg'] ) {
		echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__( 'Manual Configuration Mode:', 'nexura-ai-search-optimizer' ) . '</strong> ' . esc_html__( 'Setup Wizard skipped. You can configure /llms.txt, AI crawlers, and GEO audit settings manually below. You can re-run the Setup Wizard anytime from the top button.', 'nexura-ai-search-optimizer' ) . '</p></div>';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['naso_msg'] ) && 'welcome' === $_GET['naso_msg'] ) {
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Setup Complete!', 'nexura-ai-search-optimizer' ) . '</strong> ' . esc_html__( 'Your WordPress site is now fully optimized for ChatGPT Search, Claude, and Perplexity.', 'nexura-ai-search-optimizer' ) . '</p></div>';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved and cache refreshed successfully.', 'nexura-ai-search-optimizer' ) . '</p></div>';
	}

	// Pro Upsell Banner (Dismissible)
	( new \NexuraAISearch\Admin\Upsell() )->render_dashboard_banner();
	?>

	<nav class="nav-tab-wrapper naso-nav-tabs">
		<?php foreach ( $naso_tabs as $naso_tab_key => $naso_tab_data ) : ?>
			<a href="<?php echo esc_url( $naso_tab_data['url'] ); ?>" class="nav-tab <?php echo $naso_current_tab === $naso_tab_key ? 'nav-tab-active' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $naso_tab_data['icon'] ); ?>"></span>
				<?php echo wp_kses_post( $naso_tab_data['title'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="naso-tab-content-wrapper">
		<?php
		if ( has_action( "naso_admin_tab_{$naso_current_tab}" ) ) {
			/**
			 * Hook: naso_admin_tab_{$tab_key}
			 *
			 * Allows Pro or add-ons to render custom tab content.
			 */
			do_action( "naso_admin_tab_{$naso_current_tab}" );
		} elseif ( has_action( 'naso_admin_tab_content' ) ) {
			/**
			 * Hook: naso_admin_tab_content
			 */
			do_action( 'naso_admin_tab_content', $naso_current_tab );
		} else {
			switch ( $naso_current_tab ) {
				case 'llms-txt':
				case 'nexura-ai-search-llms':
					require_once NEXURA_AI_SEARCH_DIR . 'admin/views/tabs/tab-llms.php';
					break;
				case 'crawlers':
				case 'nexura-ai-search-crawlers':
					require_once NEXURA_AI_SEARCH_DIR . 'admin/views/tabs/tab-crawlers.php';
					break;
				case 'audit':
				case 'nexura-ai-search-audit':
					require_once NEXURA_AI_SEARCH_DIR . 'admin/views/tabs/tab-audit.php';
					break;
				case 'advanced':
				case 'nexura-ai-search-advanced':
					require_once NEXURA_AI_SEARCH_DIR . 'admin/views/tabs/tab-advanced.php';
					break;
				case 'dashboard':
				case 'nexura-ai-search':
				default:
					require_once NEXURA_AI_SEARCH_DIR . 'admin/views/tabs/tab-dashboard.php';
					break;
			}
		}
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		?>
	</div>
</div>
