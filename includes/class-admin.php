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
		if ( 'tools_page_network-performance-tweaks' !== $hook ) {
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
		if ( ! isset( $_POST['npt_settings_nonce'] ) ) {
			return;
		}
		
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['npt_settings_nonce'] ) ), 'npt_save_settings' ) ) {
			return;
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Checkboxes
		$checkboxes = array(
			'disable_dns_prefetch',
			'disable_self_pingbacks',
			'disable_google_maps',
			'disable_google_fonts',
			'enable_shortcode_cleanup',
			'cleanup_on_uninstall',
		);
		
		foreach ( $checkboxes as $key ) {
			$value = isset( $_POST[ $key ] ) ? '1' : '0';
			$this->database->update_setting( $key, $value );
		}
		
		// Numeric fields
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
					$this->database->update_setting( $key, (string) $value );
				}
			}
		}
		
		wp_safe_redirect( add_query_arg( 'npt_updated', '1', wp_get_referer() ) );
		exit;
	}

	/**
	 * Render admin page
	 *
	 * @return void
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$updated = isset( $_GET['npt_updated'] ) && '1' === $_GET['npt_updated'];
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Network & Performance Tweaks', 'network-performance-tweaks' ); ?></h1>
			
			<?php if ( $updated ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully.', 'network-performance-tweaks' ); ?></p>
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
