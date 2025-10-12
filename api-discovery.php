<?php
/**
 * Plugin Name: API & Discovery
 * Description: Control WordPress APIs and discovery features for enhanced security and privacy
 * Version: 1.0.0
 * Domain Path: /languages
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'API_DISCOVERY_VERSION', '1.0.0' );
define( 'API_DISCOVERY_DIR', plugin_dir_path( __FILE__ ) );
define( 'API_DISCOVERY_URL', plugin_dir_url( __FILE__ ) );

// Include classes
require_once API_DISCOVERY_DIR . 'includes/class-database.php';
require_once API_DISCOVERY_DIR . 'includes/class-core.php';
require_once API_DISCOVERY_DIR . 'includes/class-admin.php';

/**
 * Initialize plugin
 *
 * @since 1.0.0
 */
function api_discovery_init() {
	$database = new API_Discovery_Database();
	$core     = new API_Discovery_Core( $database );
	
	if ( is_admin() ) {
		$admin = new API_Discovery_Admin( $database, $core );
	}
}
add_action( 'plugins_loaded', 'api_discovery_init' );

/**
 * Activation hook
 *
 * @since 1.0.0
 */
function api_discovery_activate() {
	API_Discovery_Database::activate();
}
register_activation_hook( __FILE__, 'api_discovery_activate' );

/**
 * Deactivation hook
 *
 * @since 1.0.0
 */
function api_discovery_deactivate() {
	API_Discovery_Database::deactivate();
}
register_deactivation_hook( __FILE__, 'api_discovery_deactivate' );
