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
	 * Settings cache (lazy loaded)
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
	 * @return array Settings array
	 */
	private function get_settings() {
		if ( null === $this->settings ) {
			$this->settings = array(
				'disable_dns_prefetch'      => $this->database->get_setting( 'disable_dns_prefetch', '0' ),
				'disable_self_pingbacks'    => $this->database->get_setting( 'disable_self_pingbacks', '0' ),
				'disable_google_maps'       => $this->database->get_setting( 'disable_google_maps', '0' ),
				'disable_google_fonts'      => $this->database->get_setting( 'disable_google_fonts', '0' ),
				'enable_shortcode_cleanup'  => $this->database->get_setting( 'enable_shortcode_cleanup', '0' ),
				'heartbeat_frequency'       => $this->database->get_setting( 'heartbeat_frequency', '60' ),
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
		// Lazy load settings only when hooks actually fire
		add_action( 'init', array( $this, 'apply_dns_prefetch' ), 1 );
		add_action( 'pre_ping', array( $this, 'apply_self_pingbacks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'apply_google_blocks' ), 99 );
		add_filter( 'the_content', array( $this, 'apply_shortcode_cleanup' ) );
		add_filter( 'heartbeat_settings', array( $this, 'apply_heartbeat' ) );
	}

	/**
	 * Apply DNS prefetch settings
	 *
	 * @return void
	 */
	public function apply_dns_prefetch() {
		$settings = $this->get_settings();
		
		if ( '1' === $settings['disable_dns_prefetch'] ) {
			add_filter( 'wp_resource_hints', array( $this, 'disable_dns_prefetch' ), 10, 2 );
			add_action( 'wp_head', array( $this, 'remove_dns_prefetch_meta' ), 0 );
		}
	}

	/**
	 * Apply self pingback settings
	 *
	 * @param array $links Links to ping.
	 * @return void
	 */
	public function apply_self_pingbacks( &$links ) {
		$settings = $this->get_settings();
		
		if ( '1' === $settings['disable_self_pingbacks'] ) {
			$this->disable_self_pingbacks( $links );
		}
	}

	/**
	 * Apply Google service blocks
	 *
	 * @return void
	 */
	public function apply_google_blocks() {
		$settings = $this->get_settings();
		
		if ( '1' === $settings['disable_google_maps'] ) {
			$this->disable_google_maps();
		}
		
		if ( '1' === $settings['disable_google_fonts'] ) {
			$this->disable_google_fonts();
			add_filter( 'style_loader_tag', array( $this, 'filter_google_fonts' ), 10, 2 );
		}
	}

	/**
	 * Apply shortcode cleanup
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function apply_shortcode_cleanup( $content ) {
		$settings = $this->get_settings();
		
		if ( '1' === $settings['enable_shortcode_cleanup'] ) {
			return $this->clean_shortcodes( $content );
		}
		
		return $content;
	}

	/**
	 * Apply heartbeat settings
	 *
	 * @param array $settings Heartbeat settings.
	 * @return array
	 */
	public function apply_heartbeat( $settings ) {
		$plugin_settings = $this->get_settings();
		
		if ( is_numeric( $plugin_settings['heartbeat_frequency'] ) ) {
			$settings['interval'] = (int) $plugin_settings['heartbeat_frequency'];
		}
		
		return $settings;
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
		
		if ( ! isset( $wp_scripts->registered ) ) {
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
		
		if ( ! isset( $wp_styles->registered ) ) {
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
		
		$content = preg_replace_callback( "/$pattern/", function( $matches ) {
			if ( ! shortcode_exists( $matches[2] ) ) {
				return '';
			}
			return $matches[0];
		}, $content );
		
		return $content;
	}
}
