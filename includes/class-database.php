<?php
/**
 * Database operations for Network & Performance Tweaks
 *
 * @package     NetworkPerformanceTweaks
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
class NPT_Database {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Settings cache
	 *
	 * @var array|null
	 */
	private $settings_cache = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'npt_settings';
	}

	/**
	 * Create custom database table
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Use prepared statement with %i placeholder for table name (WordPress 6.2+)
		$sql = $wpdb->prepare(
			"CREATE TABLE IF NOT EXISTS %i (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				setting_key varchar(191) NOT NULL,
				setting_value longtext,
				PRIMARY KEY (id),
				UNIQUE KEY setting_key (setting_key)
			) %s",
			$this->table_name,
			$charset_collate
		);
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		
		// Verify table was created successfully
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$this->table_name
			)
		);
		
		if ( $this->table_name !== $table_exists ) {
			error_log( 'NPT: Failed to create database table - ' . $wpdb->last_error );
		}
	}

	/**
	 * Initialize default settings
	 *
	 * @return void
	 */
	public function initialize_defaults() {
		$defaults = array(
			'disable_dns_prefetch'       => '0',
			'disable_self_pingbacks'     => '0',
			'disable_google_maps'        => '0',
			'disable_google_fonts'       => '0',
			'post_revisions_limit'       => '5',
			'empty_trash_days'           => '30',
			'autosave_frequency'         => '60',
			'enable_shortcode_cleanup'   => '0',
			'heartbeat_frequency'        => '60',
			'cleanup_on_uninstall'       => '1',
		);
		
		foreach ( $defaults as $key => $value ) {
			// Only set if doesn't exist
			$existing = $this->get_setting( $key );
			if ( false === $existing ) {
				$result = $this->update_setting( $key, $value );
				if ( false === $result ) {
					error_log( 'NPT: Failed to set default for ' . $key );
				}
			}
		}
	}

	/**
	 * Get a setting value
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get_setting( $key, $default = false ) {
		if ( null === $this->settings_cache ) {
			$this->load_all_settings();
		}
		
		return isset( $this->settings_cache[ $key ] ) ? $this->settings_cache[ $key ] : $default;
	}

	/**
	 * Update a setting value
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public function update_setting( $key, $value ) {
		global $wpdb;
		
		// Use query with prepare for WordPress 6.2+ compatibility with %i placeholder
		$result = $wpdb->query(
			$wpdb->prepare(
				"REPLACE INTO %i (setting_key, setting_value) VALUES (%s, %s)",
				$this->table_name,
				$key,
				$value
			)
		);
		
		if ( false !== $result ) {
			if ( null !== $this->settings_cache ) {
				$this->settings_cache[ $key ] = $value;
			}
			return true;
		}
		
		error_log( 'NPT DB Error: ' . $wpdb->last_error );
		return false;
	}

	/**
	 * Load all settings into cache
	 *
	 * @return void
	 */
	private function load_all_settings() {
		global $wpdb;
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT setting_key, setting_value FROM %i",
				$this->table_name
			),
			ARRAY_A
		);
		
		$this->settings_cache = array();
		
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				if ( isset( $row['setting_key'] ) && isset( $row['setting_value'] ) ) {
					$this->settings_cache[ $row['setting_key'] ] = $row['setting_value'];
				}
			}
		}
	}

	/**
	 * Drop custom table
	 *
	 * @return void
	 */
	public function drop_table() {
		global $wpdb;
		
		$result = $wpdb->query( 
			$wpdb->prepare( 
				"DROP TABLE IF EXISTS %i", 
				$this->table_name 
			) 
		);
		
		if ( false === $result ) {
			error_log( 'NPT: Failed to drop table - ' . $wpdb->last_error );
		}
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_name;
	}
}
