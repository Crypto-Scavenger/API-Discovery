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

// Get cleanup preference using prepared statement
$cleanup = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT setting_value FROM %i WHERE setting_key = %s",
		$table_name,
		'cleanup_on_uninstall'
	)
);

if ( false === $cleanup ) {
	error_log( 'API Discovery Uninstall: Failed to retrieve cleanup setting - ' . $wpdb->last_error );
	// Default to not cleaning up if we can't read the setting
	$cleanup = '0';
}

if ( '1' === $cleanup ) {
	// Drop custom table using prepared statement
	$result = $wpdb->query(
		$wpdb->prepare(
			"DROP TABLE IF EXISTS %i",
			$table_name
		)
	);

	if ( false === $result ) {
		error_log( 'API Discovery Uninstall: Failed to drop table - ' . $wpdb->last_error );
	}

	// Clean transients using prepared statement with wildcards
	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_api_discovery_' ) . '%'
		)
	);

	if ( false === $deleted ) {
		error_log( 'API Discovery Uninstall: Failed to delete transients - ' . $wpdb->last_error );
	}

	wp_cache_flush();
}
