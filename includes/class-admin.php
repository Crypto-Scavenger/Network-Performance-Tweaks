<?php
/**
 * Admin interface for Network & Performance Tweaks
 *
 * @package     NetworkPerformanceTweaks
 * @subpackage  Admin
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin interface class
 *
 * @since 1.0.0
 */
class NPT_Admin {

	/**
	 * Database instance
	 *
	 * @var NPT_Database
	 */
	private $database;

	/**
	 * Admin page hook suffix
	 *
	 * @var string
	 */
	private $page_hook = 'tools_page_network-performance-tweaks';

	/**
	 * Constructor
	 *
	 * @param NPT_Database $database Database instance.
	 */
	public function __construct( $database ) {
		$this->database = $database;
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Network & Performance Tweaks', 'network-performance-tweaks' ),
			__( 'Network & Performance', 'network-performance-tweaks' ),
			'manage_options',
			'network-performance-tweaks',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( $this->page_hook !== $hook ) {
			return;
		}
		
		wp_enqueue_style(
			'npt-admin',
			NPT_URL . 'assets/admin.css',
			array(),
			NPT_VERSION
		);
	}

	/**
	 * Handle form submission
	 *
	 * @return void
	 */
	public function handle_form_submission() {
		// Check if nonce field exists
		if ( ! isset( $_POST['npt_settings_nonce'] ) ) {
			return;
		}
		
		// Verify nonce with sanitization
		$nonce = sanitize_text_field( wp_unslash( $_POST['npt_settings_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'npt_save_settings' ) ) {
			return;
		}
		
		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Save settings
		$result = $this->save_settings();
		
		// Prepare redirect URL with status
		$redirect_url = add_query_arg(
			array(
				'page' => 'network-performance-tweaks',
				'npt_updated' => is_wp_error( $result ) ? '0' : '1',
			),
			admin_url( 'tools.php' )
		);
		
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Save settings with validation
	 *
	 * @return bool|WP_Error
	 */
	private function save_settings() {
		// Checkboxes
		$checkboxes = array(
			'disable_dns_prefetch',
			'disable_self_pingbacks',
			'disable_google_maps',
			'disable_google_fonts',
			'enable_shortcode_cleanup',
			'cleanup_on_uninstall',
		);
		
		$errors = array();
		
		foreach ( $checkboxes as $key ) {
			$value = isset( $_POST[ $key ] ) ? '1' : '0';
			$result = $this->database->update_setting( $key, $value );
			
			if ( is_wp_error( $result ) ) {
				$errors[] = $key . ': ' . $result->get_error_message();
			}
		}
		
		// Numeric fields with validation
		$numeric_fields = array(
			'post_revisions_limit' => array( 'min' => 0, 'max' => 100 ),
			'empty_trash_days' => array( 'min' => 0, 'max' => 365 ),
			'autosave_frequency' => array( 'min' => 10, 'max' => 3600 ),
			'heartbeat_frequency' => array( 'min' => 15, 'max' => 300 ),
		);
		
		foreach ( $numeric_fields as $key => $limits ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = absint( $_POST[ $key ] );
				if ( $value >= $limits['min'] && $value <= $limits['max'] ) {
					$result = $this->database->update_setting( $key, (string) $value );
					
					if ( is_wp_error( $result ) ) {
						$errors[] = $key . ': ' . $result->get_error_message();
					}
				} else {
					$errors[] = sprintf(
						'%s: Value must be between %d and %d',
						$key,
						$limits['min'],
						$limits['max']
					);
				}
			}
		}
		
		if ( ! empty( $errors ) ) {
			error_log( 'NPT: Errors saving settings - ' . implode( ', ', $errors ) );
			return new WP_Error(
				'save_error',
				__( 'Some settings failed to save', 'network-performance-tweaks' )
			);
		}
		
		return true;
	}

	/**
	 * Render admin page
	 *
	 * @return void
	 */
	public function render_admin_page() {
		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access', 'network-performance-tweaks' ) );
		}
		
		// Sanitize GET parameter
		$updated = '0';
		if ( isset( $_GET['npt_updated'] ) ) {
			$updated = sanitize_text_field( wp_unslash( $_GET['npt_updated'] ) );
		}
		
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Network & Performance Tweaks', 'network-performance-tweaks' ); ?></h1>
			
			<?php if ( '1' === $updated ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully.', 'network-performance-tweaks' ); ?></p>
				</div>
			<?php elseif ( '0' === $updated && isset( $_GET['npt_updated'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Failed to save some settings. Please try again.', 'network-performance-tweaks' ); ?></p>
				</div>
			<?php endif; ?>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'npt_save_settings', 'npt_settings_nonce' ); ?>
				
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Network Optimizations', 'network-performance-tweaks' ); ?>
							</th>
							<td>
								<fieldset>
									<label>
										<input type="checkbox" name="disable_dns_prefetch" value="1" <?php checked( $this->database->get_setting( 'disable_dns_prefetch' ), '1' ); ?>>
										<?php esc_html_e( 'Disable DNS Prefetching', 'network-performance-tweaks' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'Removes DNS prefetch to fonts.googleapis.com and s.w.org to reduce external connections.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
								
								<fieldset>
									<label>
										<input type="checkbox" name="disable_self_pingbacks" value="1" <?php checked( $this->database->get_setting( 'disable_self_pingbacks' ), '1' ); ?>>
										<?php esc_html_e( 'Disable Self Pingbacks', 'network-performance-tweaks' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'Prevents WordPress from notifying itself when you link to your own posts.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
								
								<fieldset>
									<label>
										<input type="checkbox" name="disable_google_maps" value="1" <?php checked( $this->database->get_setting( 'disable_google_maps' ), '1' ); ?>>
										<?php esc_html_e( 'Disable Google Maps API', 'network-performance-tweaks' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'Removes Google Maps scripts loaded by themes or plugins.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
								
								<fieldset>
									<label>
										<input type="checkbox" name="disable_google_fonts" value="1" <?php checked( $this->database->get_setting( 'disable_google_fonts' ), '1' ); ?>>
										<?php esc_html_e( 'Disable Google Fonts', 'network-performance-tweaks' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'Removes Google Fonts loading from external servers.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Content Management', 'network-performance-tweaks' ); ?>
							</th>
							<td>
								<fieldset>
									<label for="post_revisions_limit">
										<?php esc_html_e( 'Post Revisions Limit', 'network-performance-tweaks' ); ?>
									</label>
									<input type="number" id="post_revisions_limit" name="post_revisions_limit" value="<?php echo esc_attr( $this->database->get_setting( 'post_revisions_limit', '5' ) ); ?>" min="0" max="100" class="small-text">
									<p class="description">
										<?php esc_html_e( 'Maximum number of revisions to keep per post/page. 0 to disable revisions.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
								
								<fieldset>
									<label for="empty_trash_days">
										<?php esc_html_e( 'Empty Trash Days', 'network-performance-tweaks' ); ?>
									</label>
									<input type="number" id="empty_trash_days" name="empty_trash_days" value="<?php echo esc_attr( $this->database->get_setting( 'empty_trash_days', '30' ) ); ?>" min="0" max="365" class="small-text">
									<p class="description">
										<?php esc_html_e( 'Number of days before permanently deleting trashed items. 0 to delete immediately.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
								
								<fieldset>
									<label for="autosave_frequency">
										<?php esc_html_e( 'Autosave Frequency (seconds)', 'network-performance-tweaks' ); ?>
									</label>
									<input type="number" id="autosave_frequency" name="autosave_frequency" value="<?php echo esc_attr( $this->database->get_setting( 'autosave_frequency', '60' ) ); ?>" min="10" max="3600" class="small-text">
									<p class="description">
										<?php esc_html_e( 'How often WordPress automatically saves your work while editing. Default: 60 seconds.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
								
								<fieldset>
									<label>
										<input type="checkbox" name="enable_shortcode_cleanup" value="1" <?php checked( $this->database->get_setting( 'enable_shortcode_cleanup' ), '1' ); ?>>
										<?php esc_html_e( 'Enable Shortcode Cleanup', 'network-performance-tweaks' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'Removes leftover shortcodes from deactivated plugins to clean up content display.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Admin Performance', 'network-performance-tweaks' ); ?>
							</th>
							<td>
								<fieldset>
									<label for="heartbeat_frequency">
										<?php esc_html_e( 'Heartbeat Frequency (seconds)', 'network-performance-tweaks' ); ?>
									</label>
									<input type="number" id="heartbeat_frequency" name="heartbeat_frequency" value="<?php echo esc_attr( $this->database->get_setting( 'heartbeat_frequency', '60' ) ); ?>" min="15" max="300" class="small-text">
									<p class="description">
										<?php esc_html_e( 'Controls how often WordPress checks for updates and notifications in admin. Default: 60 seconds.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Uninstall Options', 'network-performance-tweaks' ); ?>
							</th>
							<td>
								<fieldset>
									<label>
										<input type="checkbox" name="cleanup_on_uninstall" value="1" <?php checked( $this->database->get_setting( 'cleanup_on_uninstall', '1' ), '1' ); ?>>
										<?php esc_html_e( 'Remove all data on uninstall', 'network-performance-tweaks' ); ?>
									</label>
									<p class="description">
										<?php esc_html_e( 'If checked, all plugin data will be removed when the plugin is uninstalled.', 'network-performance-tweaks' ); ?>
									</p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
