<?php
/**
 * Uninstall handler for Network & Performance Tweaks
 *
 * @package NetworkPerformanceTweaks
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Include database class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

// Initialize database
$database = new NPT_Database();

// Check if user wants to cleanup
$cleanup = $database->get_setting( 'cleanup_on_uninstall', '1' );

if ( '1' === $cleanup ) {
	// Drop custom table
	$database->drop_table();
	
	// Clear any transients
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_npt_' ) . '%'
		)
	);
	
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_timeout_npt_' ) . '%'
		)
	);
	
	// Clear object cache
	wp_cache_flush();
}
