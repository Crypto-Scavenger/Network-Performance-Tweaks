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

// If setting retrieval failed, default to NOT cleaning up (safer)
if ( false === $cleanup ) {
	error_log( 'NPT Uninstall: Could not retrieve cleanup setting, skipping cleanup for safety' );
	return;
}

if ( '1' === $cleanup ) {
	global $wpdb;
	
	// Drop custom table using prepared statement with %i placeholder
	$result = $database->drop_table();
	
	if ( false === $result ) {
		error_log( 'NPT Uninstall: Failed to drop custom table' );
	}
	
	// Clear transients using prepared statement with wildcards
	// Use %i placeholder for table name
	$transient_result = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM %i 
			WHERE option_name LIKE %s 
			OR option_name LIKE %s",
			$wpdb->options,
			$wpdb->esc_like( '_transient_npt_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_npt_' ) . '%'
		)
	);
	
	if ( false === $transient_result ) {
		error_log( 'NPT Uninstall: Failed to delete transients - ' . $wpdb->last_error );
	}
	
	// Clear object cache
	wp_cache_flush();
}
