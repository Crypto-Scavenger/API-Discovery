<?php
/**
 * Uninstall handler for API & Discovery plugin
 *
 * @package API_Discovery
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

global $wpdb;
$table_name = $wpdb->prefix . 'api_discovery_settings';

$cleanup = $wpdb->get_var( $wpdb->prepare(
	"SELECT setting_value FROM `{$table_name}` WHERE setting_key = %s",
	'cleanup_on_uninstall'
) );

if ( '1' === $cleanup ) {
	$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
	
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM {$wpdb->options} 
		WHERE option_name LIKE %s",
		$wpdb->esc_like( '_transient_api_discovery_' ) . '%'
	) );
	
	wp_cache_flush();
}
