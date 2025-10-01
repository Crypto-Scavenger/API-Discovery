<?php
/**
 * Database operations for API & Discovery plugin
 *
 * @package     API_Discovery
 * @subpackage  Database
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all database operations
 *
 * @since 1.0.0
 */
class API_Discovery_Database {

	/**
	 * Table name for settings
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'api_discovery_settings';
	}

	/**
	 * Activation handler - creates database table
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'api_discovery_settings';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(191) NOT NULL,
			setting_value longtext,
			PRIMARY KEY (id),
			UNIQUE KEY setting_key (setting_key)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Set default values
		$defaults = array(
			'disable_rest_api_frontend' => '0',
			'disable_xmlrpc'            => '0',
			'disable_rsd'               => '0',
			'disable_wlw'               => '0',
			'disable_feed_links'        => '0',
			'disable_feeds'             => '0',
			'disable_feed_generator'    => '0',
			'cleanup_on_uninstall'      => '0',
		);

		$instance = new self();
		foreach ( $defaults as $key => $value ) {
			$existing = $instance->get_setting( $key );
			if ( false === $existing ) {
				$instance->save_setting( $key, $value );
			}
		}
	}

	/**
	 * Deactivation handler
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Cleanup transients
		delete_transient( 'api_discovery_settings_cache' );
	}

	/**
	 * Get a setting value
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     Setting key
	 * @param mixed  $default Default value if not found
	 * @return mixed Setting value or default
	 */
	public function get_setting( $key, $default = false ) {
		global $wpdb;

		$value = wp_cache_get( 'setting_' . $key, 'api_discovery' );
		if ( false !== $value ) {
			return maybe_unserialize( $value );
		}

		$result = $wpdb->get_var( $wpdb->prepare(
			"SELECT setting_value FROM `{$this->table_name}` WHERE setting_key = %s",
			$key
		) );

		if ( null === $result ) {
			return $default;
		}

		wp_cache_set( 'setting_' . $key, $result, 'api_discovery', 3600 );

		return maybe_unserialize( $result );
	}

	/**
	 * Save a setting value
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Setting key
	 * @param mixed  $value Setting value
	 * @return bool|WP_Error Success or error object
	 */
	public function save_setting( $key, $value ) {
		global $wpdb;

		$result = $wpdb->replace(
			$this->table_name,
			array(
				'setting_key'   => $key,
				'setting_value' => maybe_serialize( $value ),
			),
			array( '%s', '%s' )
		);

		if ( false === $result ) {
			error_log( 'API Discovery DB Error: ' . $wpdb->last_error );
			return new WP_Error(
				'db_error',
				__( 'Failed to save setting', 'api-discovery' )
			);
		}

		wp_cache_delete( 'setting_' . $key, 'api_discovery' );
		delete_transient( 'api_discovery_settings_cache' );

		return true;
	}

	/**
	 * Get all settings as array
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings array
	 */
	public function get_all_settings() {
		$cached = get_transient( 'api_discovery_settings_cache' );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$results = $wpdb->get_results(
			"SELECT setting_key, setting_value FROM `{$this->table_name}`",
			ARRAY_A
		);

		$settings = array();
		if ( $results ) {
			foreach ( $results as $row ) {
				$settings[ $row['setting_key'] ] = maybe_unserialize( $row['setting_value'] );
			}
		}

		set_transient( 'api_discovery_settings_cache', $settings, 12 * HOUR_IN_SECONDS );

		return $settings;
	}

	/**
	 * Delete all settings and table
	 *
	 * @since 1.0.0
	 */
	public function cleanup() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS `{$this->table_name}`" );

		delete_transient( 'api_discovery_settings_cache' );
		wp_cache_flush();
	}
}
