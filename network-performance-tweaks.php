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

// Define WordPress constants IMMEDIATELY (before WordPress initializes)
// These constants MUST be set early, not in plugins_loaded hook
$npt_database = new NPT_Database();

$revisions = $npt_database->get_setting( 'post_revisions_limit', '5' );
if ( ! defined( 'WP_POST_REVISIONS' ) && is_numeric( $revisions ) ) {
	define( 'WP_POST_REVISIONS', (int) $revisions );
}

$trash_days = $npt_database->get_setting( 'empty_trash_days', '30' );
if ( ! defined( 'EMPTY_TRASH_DAYS' ) && is_numeric( $trash_days ) ) {
	define( 'EMPTY_TRASH_DAYS', (int) $trash_days );
}

$autosave = $npt_database->get_setting( 'autosave_frequency', '60' );
if ( ! defined( 'AUTOSAVE_INTERVAL' ) && is_numeric( $autosave ) ) {
	define( 'AUTOSAVE_INTERVAL', (int) $autosave );
}

// Initialize plugin on plugins_loaded
function npt_init() {
	global $npt_database;
	
	$core = new NPT_Core( $npt_database );
	
	if ( is_admin() ) {
		$admin = new NPT_Admin( $npt_database );
	}
}
add_action( 'plugins_loaded', 'npt_init' );

// Activation hook
function npt_activate() {
	$database = new NPT_Database();
	$database->create_table();
	$database->initialize_defaults();
}
register_activation_hook( __FILE__, 'npt_activate' );
