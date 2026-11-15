<?php
/**
 * Main Plugin Orchestrator
 *
 * @package NexuraAISearch
 */

namespace NexuraAISearch;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin bootstrap class.
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	public $settings;

	/**
	 * Cache instance.
	 *
	 * @var Cache
	 */
	public $cache;

	/**
	 * Rewrite rules instance.
	 *
	 * @var Rewrite_Rules
	 */
	public $rewrite_rules;

	/**
	 * Markdown output instance.
	 *
	 * @var Markdown_Output
	 */
	public $markdown;

	/**
	 * Crawler policy instance.
	 *
	 * @var Crawler_Policy
	 */
	public $crawler_policy;

	/**
	 * Admin instance.
	 *
	 * @var \NexuraAISearch\Admin\Admin|null
	 */
	public $admin = null;

	/**
	 * Meta box instance.
	 *
	 * @var Meta_Box|null
	 */
	public $meta_box = null;

	/**
	 * List table columns instance.
	 *
	 * @var List_Table_Columns|null
	 */
	public $list_table_columns = null;

	/**
	 * Background scanner instance.
	 *
	 * @var Background_Scanner|null
	 */
	public $background_scanner = null;

	/**
	 * Retrieve singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load all core dependencies.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-settings.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-cache.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-rewrite-rules.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-llms-generator.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-markdown-output.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-schema-diagnostics.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-geo-audit.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-crawler-policy.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-email-limiter.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-export-pdf.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-ai-prompt-fix.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-meta-box.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-list-table-columns.php';
		require_once NEXURA_AI_SEARCH_DIR . 'includes/class-background-scanner.php';

		$this->settings           = new Settings();
		$this->cache              = new Cache();
		$this->rewrite_rules      = new Rewrite_Rules();
		$this->markdown           = new Markdown_Output();
		$this->crawler_policy     = new Crawler_Policy();
		$this->background_scanner = new Background_Scanner();

		if ( is_admin() ) {
			require_once NEXURA_AI_SEARCH_DIR . 'admin/class-admin.php';
			require_once NEXURA_AI_SEARCH_DIR . 'admin/class-onboarding.php';
			require_once NEXURA_AI_SEARCH_DIR . 'admin/class-upsell.php';

			$this->admin              = new \NexuraAISearch\Admin\Admin();
			$this->meta_box           = new Meta_Box();
			$this->list_table_columns = new List_Table_Columns();
		}
	}

	/**
	 * Run the plugin lifecycle.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'admin_init', array( $this->settings, 'register' ) );

		$this->cache->register_hooks();
		$this->rewrite_rules->register_hooks();
		$this->markdown->register_hooks();
		$this->crawler_policy->register_hooks();
		$this->background_scanner->register_hooks();

		if ( is_admin() ) {
			if ( $this->admin ) {
				$this->admin->register_hooks();
			}
			if ( $this->meta_box ) {
				$this->meta_box->register_hooks();
			}
			if ( $this->list_table_columns ) {
				$this->list_table_columns->register_hooks();
			}
		}
	}
}
