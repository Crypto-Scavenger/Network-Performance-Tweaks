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
	
	// Clear any transients using prepared statements with %i placeholder
	global $wpdb;
	
	// Delete transient options
	$result = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM %i 
			WHERE option_name LIKE %s",
			$wpdb->options,
			$wpdb->esc_like( '_transient_npt_' ) . '%'
		)
	);
	
	if ( false === $result ) {
		error_log( 'NPT Uninstall: Failed to delete transients - ' . $wpdb->last_error );
	}
	
	// Delete transient timeout options
	$result = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM %i 
			WHERE option_name LIKE %s",
			$wpdb->options,
			$wpdb->esc_like( '_transient_timeout_npt_' ) . '%'
		)
	);
	
	if ( false === $result ) {
		error_log( 'NPT Uninstall: Failed to delete transient timeouts - ' . $wpdb->last_error );
	}
	
	// Clear object cache
	wp_cache_flush();
}
