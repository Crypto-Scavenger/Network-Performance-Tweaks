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
		
		$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(191) NOT NULL,
			setting_value longtext,
			PRIMARY KEY (id),
			UNIQUE KEY setting_key (setting_key)
		) $charset_collate;";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Initialize default settings
	 *
	 * @return void
	 */
	public function initialize_defaults() {
		$defaults = array(
			'disable_dns_prefetch' => '0',
			'disable_self_pingbacks' => '0',
			'disable_google_maps' => '0',
			'disable_google_fonts' => '0',
			'post_revisions_limit' => '5',
			'empty_trash_days' => '30',
			'autosave_frequency' => '60',
			'enable_shortcode_cleanup' => '0',
			'heartbeat_frequency' => '60',
			'cleanup_on_uninstall' => '1',
		);
		
		foreach ( $defaults as $key => $value ) {
			$this->update_setting( $key, $value );
		}
	}

	/**
	 * Get a setting value
	 *
	 * @param string $key Setting key.
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
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public function update_setting( $key, $value ) {
		global $wpdb;
		
		$result = $wpdb->replace(
			$this->table_name,
			array(
				'setting_key' => $key,
				'setting_value' => $value,
			),
			array( '%s', '%s' )
		);
		
		if ( false !== $result ) {
			$this->settings_cache[ $key ] = $value;
			return true;
		}
		
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
				"SELECT setting_key, setting_value FROM {$this->table_name} WHERE 1=%d",
				1
			),
			ARRAY_A
		);
		
		$this->settings_cache = array();
		
		if ( $results ) {
			foreach ( $results as $row ) {
				$this->settings_cache[ $row['setting_key'] ] = $row['setting_value'];
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
		
		$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %i", $this->table_name ) );
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
