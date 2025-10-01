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

		$sql = $wpdb->prepare(
			"CREATE TABLE IF NOT EXISTS %i (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				setting_key varchar(191) NOT NULL,
				setting_value longtext,
				PRIMARY KEY (id),
				UNIQUE KEY setting_key (setting_key)
			) %s",
			$table_name,
			$charset_collate
		);

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$result = dbDelta( $sql );

		// Verify table creation
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
			error_log( 'API Discovery: Failed to create database table' );
			return;
		}

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
			$existing = $instance->get_setting( $key, null );
			if ( null === $existing ) {
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
		delete_transient( 'api_discovery_all_settings' );
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
	public function get_setting( $key, $default = '0' ) {
		global $wpdb;

		$value = wp_cache_get( 'setting_' . $key, 'api_discovery' );
		if ( false !== $value ) {
			return maybe_unserialize( $value );
		}

		$result = $wpdb->get_var( $wpdb->prepare(
			"SELECT setting_value FROM %i WHERE setting_key = %s",
			$this->table_name,
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
		delete_transient( 'api_discovery_all_settings' );

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
		$cached = get_transient( 'api_discovery_all_settings' );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT setting_key, setting_value FROM %i", $this->table_name ),
			ARRAY_A
		);

		$settings = array();
		if ( $results ) {
			foreach ( $results as $row ) {
				$settings[ $row['setting_key'] ] = maybe_unserialize( $row['setting_value'] );
			}
		}

		// Merge with defaults to handle new settings after plugin updates
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

		$settings = wp_parse_args( $settings, $defaults );

		set_transient( 'api_discovery_all_settings', $settings, 12 * HOUR_IN_SECONDS );

		return $settings;
	}

	/**
	 * Delete all settings and table
	 *
	 * @since 1.0.0
	 */
	public function cleanup() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %i", $this->table_name ) );

		delete_transient( 'api_discovery_all_settings' );
		wp_cache_flush();
	}
}
