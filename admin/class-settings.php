<?php
/**
 * Admin Settings Alias
 *
 * @package NexuraAISearch\Admin
 */

namespace NexuraAISearch\Admin;

use NexuraAISearch\Settings as CoreSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings wrapper class for admin subsystem.
 */
class Settings extends CoreSettings {
	// Inherits full Settings API registration and sanitization logic from CoreSettings.
}
