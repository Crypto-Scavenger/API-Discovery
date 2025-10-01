<?php
/**
 * Admin interface for API & Discovery plugin
 *
 * @package     API_Discovery
 * @subpackage  Admin
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin interface
 *
 * @since 1.0.0
 */
class API_Discovery_Admin {

	/**
	 * Database instance
	 *
	 * @var API_Discovery_Database
	 */
	private $database;

	/**
	 * Core instance
	 *
	 * @var API_Discovery_Core
	 */
	private $core;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param API_Discovery_Database $database Database instance
	 * @param API_Discovery_Core     $core     Core instance
	 */
	public function __construct( $database, $core ) {
		$this->database = $database;
		$this->core     = $core;
		$this->init_hooks();
	}

	/**
	 * Initialize admin hooks
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
	}

	/**
	 * Add admin menu item
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'API & Discovery', 'api-discovery' ),
			__( 'API & Discovery', 'api-discovery' ),
			'manage_options',
			'api-discovery',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'tools_page_api-discovery' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'api-discovery-admin',
			API_DISCOVERY_URL . 'assets/admin.css',
			array(),
			API_DISCOVERY_VERSION
		);
	}

	/**
	 * Handle form submission
	 *
	 * @since 1.0.0
	 */
	public function handle_form_submission() {
		if ( ! isset( $_POST['api_discovery_save'] ) ) {
			return;
		}

		$this->save_settings();
	}

	/**
	 * Render admin page
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access', 'api-discovery' ) );
		}

		$settings = $this->database->get_all_settings();
		$message  = '';

		// Sanitize GET parameter
		$updated = isset( $_GET['updated'] ) ? sanitize_text_field( wp_unslash( $_GET['updated'] ) ) : '';
		if ( '1' === $updated ) {
			$message = '<div class="notice notice-success is-dismissible"><p>' .
			           esc_html__( 'Settings saved successfully.', 'api-discovery' ) .
			           '</p></div>';
		}

		?>
		<div class="wrap api-discovery-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php echo wp_kses_post( $message ); ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'api_discovery_save_settings', 'api_discovery_nonce' ); ?>
				
				<h2><?php esc_html_e( 'API & Discovery Settings', 'api-discovery' ); ?></h2>
				
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable REST API (Frontend)', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_rest_api_frontend" 
									value="1"
									<?php checked( '1', $settings['disable_rest_api_frontend'] ); ?>
								/>
								<?php esc_html_e( 'Removes REST API links from frontend. API still works, just removes discovery links.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable XML-RPC', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_xmlrpc" 
									value="1"
									<?php checked( '1', $settings['disable_xmlrpc'] ); ?>
								/>
								<?php esc_html_e( 'Disables legacy XML-RPC protocol. Improves security by reducing attack vectors.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable Really Simple Discovery (RSD)', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_rsd" 
									value="1"
									<?php checked( '1', $settings['disable_rsd'] ); ?>
								/>
								<?php esc_html_e( 'Removes RSD links for deprecated blog clients.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable Windows Live Writer Manifest', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_wlw" 
									value="1"
									<?php checked( '1', $settings['disable_wlw'] ); ?>
								/>
								<?php esc_html_e( 'Removes support for obsolete Microsoft Windows Live Writer software.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable RSS Feed Links', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_feed_links" 
									value="1"
									<?php checked( '1', $settings['disable_feed_links'] ); ?>
								/>
								<?php esc_html_e( 'Removes feed links from site header. Feeds still work if accessed directly.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable RSS Feeds Completely', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_feeds" 
									value="1"
									<?php checked( '1', $settings['disable_feeds'] ); ?>
								/>
								<?php esc_html_e( 'Completely disables all RSS feeds. Use only if you don\'t need feeds at all.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Disable Feed Generator Tags', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="disable_feed_generator" 
									value="1"
									<?php checked( '1', $settings['disable_feed_generator'] ); ?>
								/>
								<?php esc_html_e( 'Removes WordPress version information from RSS feeds.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Plugin Data', 'api-discovery' ); ?></h2>
				
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Cleanup on Uninstall', 'api-discovery' ); ?>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="cleanup_on_uninstall" 
									value="1"
									<?php checked( '1', $settings['cleanup_on_uninstall'] ); ?>
								/>
								<?php esc_html_e( 'Remove all plugin data when uninstalling. Leave unchecked to preserve settings.', 'api-discovery' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<input type="hidden" name="api_discovery_save" value="1" />
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save settings with security checks
	 *
	 * @since 1.0.0
	 */
	private function save_settings() {
		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access', 'api-discovery' ) );
		}

		// Verify nonce - SANITIZE INPUT
		if ( ! isset( $_POST['api_discovery_nonce'] ) ) {
			wp_die( esc_html__( 'Security check failed', 'api-discovery' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['api_discovery_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'api_discovery_save_settings' ) ) {
			wp_die( esc_html__( 'Security check failed', 'api-discovery' ) );
		}

		$settings = array(
			'disable_rest_api_frontend' => isset( $_POST['disable_rest_api_frontend'] ) ? '1' : '0',
			'disable_xmlrpc'            => isset( $_POST['disable_xmlrpc'] ) ? '1' : '0',
			'disable_rsd'               => isset( $_POST['disable_rsd'] ) ? '1' : '0',
			'disable_wlw'               => isset( $_POST['disable_wlw'] ) ? '1' : '0',
			'disable_feed_links'        => isset( $_POST['disable_feed_links'] ) ? '1' : '0',
			'disable_feeds'             => isset( $_POST['disable_feeds'] ) ? '1' : '0',
			'disable_feed_generator'    => isset( $_POST['disable_feed_generator'] ) ? '1' : '0',
			'cleanup_on_uninstall'      => isset( $_POST['cleanup_on_uninstall'] ) ? '1' : '0',
		);

		foreach ( $settings as $key => $value ) {
			$result = $this->database->save_setting( $key, $value );
			if ( is_wp_error( $result ) ) {
				wp_die( esc_html( $result->get_error_message() ) );
			}
		}

		wp_safe_redirect(
			add_query_arg(
				'updated',
				'1',
				admin_url( 'tools.php?page=api-discovery' )
			)
		);
		exit;
	}
}
