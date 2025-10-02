<?php
/**
 * Plugin Name: Network & Performance Tweaks
 * Description: Optimize WordPress network requests and performance settings. Disable external dependencies, control revisions, and fine-tune WordPress behavior.
 * Version: 1.0.1
 * Text Domain: network-performance-tweaks
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'NPT_VERSION', '1.0.1' );
define( 'NPT_DIR', plugin_dir_path( __FILE__ ) );
define( 'NPT_URL', plugin_dir_url( __FILE__ ) );
define( 'NPT_FILE', __FILE__ );

// Include classes
require_once NPT_DIR . 'includes/class-database.php';
require_once NPT_DIR . 'includes/class-core.php';
require_once NPT_DIR . 'includes/class-admin.php';

// Initialize plugin
function npt_init() {
	// Error handling for class loading
	if ( ! class_exists( 'NPT_Database' ) || ! class_exists( 'NPT_Core' ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'NPT: Failed to load required classes' );
		}
		return;
	}
	
	$database = new NPT_Database();
	$core = new NPT_Core( $database );
	
	if ( is_admin() ) {
		if ( ! class_exists( 'NPT_Admin' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'NPT: Failed to load admin class' );
			}
			return;
		}
		$admin = new NPT_Admin( $database );
	}
}
add_action( 'plugins_loaded', 'npt_init' );

// Activation hook
function npt_activate() {
	if ( ! class_exists( 'NPT_Database' ) ) {
		wp_die( esc_html__( 'Failed to activate plugin: Database class not found', 'network-performance-tweaks' ) );
	}
	
	$database = new NPT_Database();
	$result = $database->create_table();
	
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ) );
	}
	
	$database->initialize_defaults();
}
register_activation_hook( __FILE__, 'npt_activate' );

// Deactivation hook
function npt_deactivate() {
	// Clear transients
	delete_transient( 'npt_settings_cache' );
	
	// Clear object cache
	wp_cache_flush();
}
register_deactivation_hook( __FILE__, 'npt_deactivate' );
