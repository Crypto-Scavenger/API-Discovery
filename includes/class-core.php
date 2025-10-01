<?php
/**
 * Core functionality for API & Discovery plugin
 *
 * @package     API_Discovery
 * @subpackage  Core
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles core plugin functionality
 *
 * @since 1.0.0
 */
class API_Discovery_Core {

	/**
	 * Database instance
	 *
	 * @var API_Discovery_Database
	 */
	private $database;

	/**
	 * Settings cache
	 *
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param API_Discovery_Database $database Database instance
	 */
	public function __construct( $database ) {
		$this->database = $database;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		// Apply settings on init - must happen early to remove WordPress default actions/filters
		add_action( 'init', array( $this, 'apply_settings' ), 1 );
	}

	/**
	 * Get settings (lazy loading)
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings array
	 */
	private function get_settings() {
		if ( null === $this->settings ) {
			$this->settings = $this->database->get_all_settings();
		}
		return $this->settings;
	}

	/**
	 * Apply settings by adding appropriate hooks
	 *
	 * @since 1.0.0
	 */
	public function apply_settings() {
		$settings = $this->get_settings();

		// Disable REST API frontend links
		if ( '1' === $settings['disable_rest_api_frontend'] ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
		}

		// Disable XML-RPC
		if ( '1' === $settings['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', array( $this, 'remove_xmlrpc_pingback_header' ) );
		}

		// Disable RSD
		if ( '1' === $settings['disable_rsd'] ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		// Disable Windows Live Writer
		if ( '1' === $settings['disable_wlw'] ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		// Disable feed links
		if ( '1' === $settings['disable_feed_links'] ) {
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}

		// Disable feeds completely
		if ( '1' === $settings['disable_feeds'] ) {
			add_action( 'do_feed', array( $this, 'disable_feed' ), 1 );
			add_action( 'do_feed_rdf', array( $this, 'disable_feed' ), 1 );
			add_action( 'do_feed_rss', array( $this, 'disable_feed' ), 1 );
			add_action( 'do_feed_rss2', array( $this, 'disable_feed' ), 1 );
			add_action( 'do_feed_atom', array( $this, 'disable_feed' ), 1 );
			add_action( 'do_feed_rss2_comments', array( $this, 'disable_feed' ), 1 );
			add_action( 'do_feed_atom_comments', array( $this, 'disable_feed' ), 1 );
		}

		// Disable feed generator tags
		if ( '1' === $settings['disable_feed_generator'] ) {
			remove_action( 'rss2_head', 'the_generator' );
			remove_action( 'commentsrss2_head', 'the_generator' );
			remove_action( 'rss_head', 'the_generator' );
			remove_action( 'rdf_header', 'the_generator' );
			remove_action( 'atom_head', 'the_generator' );
			remove_action( 'opml_head', 'the_generator' );
			remove_action( 'app_head', 'the_generator' );
		}
	}

	/**
	 * Remove X-Pingback header
	 *
	 * @since 1.0.0
	 *
	 * @param array $headers HTTP headers
	 * @return array Modified headers
	 */
	public function remove_xmlrpc_pingback_header( $headers ) {
		if ( isset( $headers['X-Pingback'] ) ) {
			unset( $headers['X-Pingback'] );
		}
		return $headers;
	}

	/**
	 * Disable feed access
	 *
	 * @since 1.0.0
	 */
	public function disable_feed() {
		wp_die(
			esc_html__( 'Feeds are disabled on this site.', 'api-discovery' ),
			esc_html__( 'Feed Disabled', 'api-discovery' ),
			array( 'response' => 410 )
		);
	}
}
