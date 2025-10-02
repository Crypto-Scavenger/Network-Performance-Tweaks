<?php
/**
 * Core functionality for Network & Performance Tweaks
 *
 * @package     NetworkPerformanceTweaks
 * @subpackage  Core
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core functionality class
 *
 * @since 1.0.0
 */
class NPT_Core {

	/**
	 * Database instance
	 *
	 * @var NPT_Database
	 */
	private $database;

	/**
	 * Settings cache
	 *
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Constructor
	 *
	 * @param NPT_Database $database Database instance.
	 */
	public function __construct( $database ) {
		$this->database = $database;
		$this->init_hooks();
	}

	/**
	 * Get settings with lazy loading
	 *
	 * @return array
	 */
	private function get_settings() {
		if ( null === $this->settings ) {
			$this->settings = array(
				'disable_dns_prefetch' => $this->database->get_setting( 'disable_dns_prefetch', '0' ),
				'disable_self_pingbacks' => $this->database->get_setting( 'disable_self_pingbacks', '0' ),
				'disable_google_maps' => $this->database->get_setting( 'disable_google_maps', '0' ),
				'disable_google_fonts' => $this->database->get_setting( 'disable_google_fonts', '0' ),
				'post_revisions_limit' => $this->database->get_setting( 'post_revisions_limit', '5' ),
				'empty_trash_days' => $this->database->get_setting( 'empty_trash_days', '30' ),
				'autosave_frequency' => $this->database->get_setting( 'autosave_frequency', '60' ),
				'enable_shortcode_cleanup' => $this->database->get_setting( 'enable_shortcode_cleanup', '0' ),
				'heartbeat_frequency' => $this->database->get_setting( 'heartbeat_frequency', '60' ),
			);
		}
		return $this->settings;
	}

	/**
	 * Initialize hooks
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Load settings once
		$settings = $this->get_settings();

		// DNS Prefetching
		if ( '1' === $settings['disable_dns_prefetch'] ) {
			add_filter( 'wp_resource_hints', array( $this, 'disable_dns_prefetch' ), 10, 2 );
			add_action( 'wp_head', array( $this, 'remove_dns_prefetch_meta' ), 0 );
		}

		// Self Pingbacks
		if ( '1' === $settings['disable_self_pingbacks'] ) {
			add_action( 'pre_ping', array( $this, 'disable_self_pingbacks' ) );
		}

		// Google Maps
		if ( '1' === $settings['disable_google_maps'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'disable_google_maps' ), 99 );
		}

		// Google Fonts
		if ( '1' === $settings['disable_google_fonts'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'disable_google_fonts' ), 99 );
			add_filter( 'style_loader_tag', array( $this, 'filter_google_fonts' ), 10, 2 );
		}

		// Post Revisions - validate numeric value
		$revisions = $settings['post_revisions_limit'];
		if ( ! defined( 'WP_POST_REVISIONS' ) && is_numeric( $revisions ) && $revisions >= 0 ) {
			define( 'WP_POST_REVISIONS', (int) $revisions );
		}

		// Empty Trash Days - validate numeric value
		$trash_days = $settings['empty_trash_days'];
		if ( ! defined( 'EMPTY_TRASH_DAYS' ) && is_numeric( $trash_days ) && $trash_days >= 0 ) {
			define( 'EMPTY_TRASH_DAYS', (int) $trash_days );
		}

		// Autosave Frequency - validate numeric value
		$autosave = $settings['autosave_frequency'];
		if ( ! defined( 'AUTOSAVE_INTERVAL' ) && is_numeric( $autosave ) && $autosave >= 10 ) {
			define( 'AUTOSAVE_INTERVAL', (int) $autosave );
		}

		// Shortcode Cleanup
		if ( '1' === $settings['enable_shortcode_cleanup'] ) {
			add_filter( 'the_content', array( $this, 'clean_shortcodes' ) );
		}

		// Heartbeat Frequency - validate numeric value
		$heartbeat = $settings['heartbeat_frequency'];
		if ( is_numeric( $heartbeat ) && $heartbeat >= 15 ) {
			add_filter( 'heartbeat_settings', function( $settings ) use ( $heartbeat ) {
				$settings['interval'] = (int) $heartbeat;
				return $settings;
			} );
		}
	}

	/**
	 * Disable DNS prefetching
	 *
	 * @param array  $urls URLs to prefetch.
	 * @param string $relation_type The relation type.
	 * @return array
	 */
	public function disable_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			return array();
		}
		return $urls;
	}

	/**
	 * Remove DNS prefetch meta tag
	 *
	 * @return void
	 */
	public function remove_dns_prefetch_meta() {
		echo '<meta http-equiv="x-dns-prefetch-control" content="off">' . "\n";
	}

	/**
	 * Disable self pingbacks
	 *
	 * @param array $links Links to ping.
	 * @return void
	 */
	public function disable_self_pingbacks( &$links ) {
		$home_url = get_option( 'home' );
		foreach ( $links as $key => $link ) {
			if ( 0 === strpos( $link, $home_url ) ) {
				unset( $links[ $key ] );
			}
		}
	}

	/**
	 * Disable Google Maps scripts
	 *
	 * @return void
	 */
	public function disable_google_maps() {
		global $wp_scripts;
		
		// Check if $wp_scripts is an object and has registered property
		if ( ! is_object( $wp_scripts ) || ! isset( $wp_scripts->registered ) ) {
			return;
		}
		
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( false !== strpos( $script->src, 'maps.googleapis.com' ) || 
				 false !== strpos( $script->src, 'maps.google.com' ) ) {
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Disable Google Fonts
	 *
	 * @return void
	 */
	public function disable_google_fonts() {
		global $wp_styles;
		
		// Check if $wp_styles is an object and has registered property
		if ( ! is_object( $wp_styles ) || ! isset( $wp_styles->registered ) ) {
			return;
		}
		
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( false !== strpos( $style->src, 'fonts.googleapis.com' ) ) {
				wp_dequeue_style( $handle );
				wp_deregister_style( $handle );
			}
		}
	}

	/**
	 * Filter Google Fonts from style tags
	 *
	 * @param string $html Style tag HTML.
	 * @param string $handle Style handle.
	 * @return string
	 */
	public function filter_google_fonts( $html, $handle ) {
		if ( false !== strpos( $html, 'fonts.googleapis.com' ) ) {
			return '';
		}
		return $html;
	}

	/**
	 * Clean leftover shortcodes
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function clean_shortcodes( $content ) {
		$pattern = get_shortcode_regex();
		
		$result = preg_replace_callback( "/$pattern/", function( $matches ) {
			if ( ! shortcode_exists( $matches[2] ) ) {
				return '';
			}
			return $matches[0];
		}, $content );
		
		// Handle preg_replace_callback errors
		if ( null === $result ) {
			error_log( 'NPT: preg_replace_callback failed in clean_shortcodes' );
			return $content;
		}
		
		return $result;
	}
}
