<?php

/**
 * Plugin Name:       Nexura AI Search Optimizer
 * Plugin URI:        https://wordpress.org/plugins/nexura-ai-search-optimizer
 * Description:       Optimize your WordPress content for AI search engines, ChatGPT Search, Claude, and Perplexity with /llms.txt, Clean Markdown, and GEO Audits.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Nexura Security
 * Author URI:        https://profiles.wordpress.org/prokashsarker2026/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nexura-ai-search-optimizer
 * Domain Path:       /languages
 *
 * @package           NexuraAISearch
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Plugin constants.
define( 'NEXURA_AI_SEARCH_VERSION', '1.0.0' );
define( 'NEXURA_AI_SEARCH_FILE', __FILE__ );
define( 'NEXURA_AI_SEARCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEXURA_AI_SEARCH_URL', plugin_dir_url( __FILE__ ) );
// 1. Freemius Integration (Official SDK Wrapper).
if ( function_exists( 'naso_fs' ) ) {
    naso_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'naso_fs' ) ) {
        /**
         * Helper function for Freemius SDK access.
         *
         * @return \Freemius|false
         */
        function naso_fs() {
            global $naso_fs;
            if ( !isset( $naso_fs ) ) {
                // Dynamically resolve Freemius SDK path.
                $fs_path = dirname( __FILE__ ) . '/vendor/freemius/start.php';
                if ( !file_exists( $fs_path ) ) {
                    $fs_path = dirname( __DIR__, 2 ) . '/vendor/freemius/start.php';
                }
                if ( !file_exists( $fs_path ) ) {
                    return false;
                }
                require_once $fs_path;
                $naso_fs = fs_dynamic_init( array(
                    'id'               => '42525',
                    'slug'             => 'nexura-ai-search-optimizer',
                    'type'             => 'plugin',
                    'public_key'       => 'pk_ac28920751949ac3a2471b2ab02ba',
                    'is_premium'       => false,
                    'premium_suffix'   => 'premium',
                    'has_addons'       => false,
                    'has_paid_plans'   => true,
                    'is_org_compliant' => true,
                    'menu'             => array(
                        'slug'    => 'nexura-ai-search',
                        'support' => false,
                    ),
                    'is_live'          => true,
                ) );
            }
            return $naso_fs;
        }

        // Initialize Freemius SDK.
        $naso_fs = naso_fs();
        // Redirect to Onboarding Wizard right after Freemius Opt-in or Skip.
        if ( is_object( $naso_fs ) ) {
            $naso_fs->add_filter( 'after_connect_url', function () {
                delete_transient( 'naso_activation_redirect' );
                return admin_url( 'admin.php?page=nexura-ai-onboarding' );
            } );
            $naso_fs->add_filter( 'after_skip_url', function () {
                delete_transient( 'naso_activation_redirect' );
                return admin_url( 'admin.php?page=nexura-ai-onboarding' );
            } );
            $naso_fs->add_action( 'after_uninstall', 'naso_fs_custom_uninstall' );
        }
        do_action( 'naso_fs_loaded' );
    }
}
/**
 * Cleanup database options and transient caches upon Freemius plugin uninstallation.
 *
 * @return void
 */
function naso_fs_custom_uninstall() {
    delete_option( 'nexura_ai_search_options' );
    delete_option( 'nexura_onboarding_completed' );
    delete_transient( 'naso_llms_txt' );
    delete_transient( 'naso_llms_full_txt' );
    delete_transient( 'naso_activation_redirect' );
    delete_transient( 'nexura_activation_redirect' );
    delete_transient( 'nexura_llms_txt_cache' );
    delete_transient( 'nexura_llms_full_txt_cache' );
    delete_transient( 'nexura_flush_rewrite_rules' );
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'naso_%' OR option_name LIKE 'nexura_%'" );
}

// 2. Load Core Plugin.
require_once NEXURA_AI_SEARCH_DIR . 'includes/class-activator.php';
require_once NEXURA_AI_SEARCH_DIR . 'includes/class-deactivator.php';
require_once NEXURA_AI_SEARCH_DIR . 'includes/class-plugin.php';
register_activation_hook( __FILE__, array('\\NexuraAISearch\\Activator', 'activate') );
register_deactivation_hook( __FILE__, array('\\NexuraAISearch\\Deactivator', 'deactivate') );
// Bootstrap Core Plugin.
\NexuraAISearch\Plugin::instance()->run();
// 3. Load Pro Features if Available (Strictly requires active Freemius Pro license).
if ( function_exists( 'naso_fs' ) && is_object( naso_fs() ) && naso_fs()->can_use_premium_code() ) {
    if ( file_exists( NEXURA_AI_SEARCH_DIR . 'pro/class-pro.php' ) ) {
        require_once NEXURA_AI_SEARCH_DIR . 'pro/class-pro.php';
        \NexuraAISearchPro\Pro::instance()->run();
    }
}