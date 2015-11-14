<?php
/*
* Plugin Name: WooCommerce Subscribe All the Things
* Plugin URI: https://github.com/Prospress/woocommerce-subscribe-to-all-the-things
* Description: Experimental extension for linking WooCommerce Subscriptions with product types created by other extensions, like Composites and Bundles.
* Version: 1.0.1
* Author: Prospress
* Author URI: http://prospress.com/
*
* Text Domain: woocommerce-subscribe-all-the-things
* Domain Path: /languages/
*
* Requires at least: 3.8
* Tested up to: 4.3
*
* Copyright: © 2009-2015 Prospress, Inc.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCS_ATT' ) ) :

class WCS_ATT {

	/* plugin version */
	const VERSION = '1.0.1';

	/* required WC version */
	const REQ_WC_VERSION = '2.3.0';

	/* text domain */
	const TEXT_DOMAIN = 'woocommerce-subscribe-all-the-things';

	/**
	 * @var WCS_ATT - the single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WCS_ATT Instance.
	 *
	 * Ensures only one instance of WCS_ATT is loaded or can be loaded.
	 *
	 * @static
	 * @see WCS_ATT()
	 * @return WCS_ATT - Main instance
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!' ), '1.0.0' );
	}

	/**
	 * Do some work.
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init_textdomain' ) );
		add_action( 'admin_init', array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	public function plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function plugins_loaded() {

		global $woocommerce;

		// Subs 2 check
		if ( ! function_exists( 'wcs_is_subscription' ) ) {
			add_action( 'admin_notices', array( $this, 'wcs_admin_notice' ) );
			return false;
		}

		// WC 2 check
		if ( version_compare( $woocommerce->version, self::REQ_WC_VERSION ) < 0 ) {
			add_action( 'admin_notices', array( $this, 'wc_admin_notice' ) );
			return false;
		}

		require_once( 'includes/class-wcsatt-core-compatibility.php' );
		require_once( 'includes/class-wcsatt-integrations.php' );
		require_once( 'includes/class-wcsatt-schemes.php' );
		require_once( 'includes/class-wcsatt-cart.php' );
		require_once( 'includes/class-wcsatt-display.php' );

		// Admin includes
		if ( is_admin() ) {
			$this->admin_includes();
		}

	}

	/**
	 * Loads the Admin & AJAX filters / hooks.
	 *
	 * @return void
	 */
	public function admin_includes() {

		require_once( 'includes/admin/class-wcsatt-admin.php' );
	}

	/**
	 * Display a warning message if Subs version check fails.
	 *
	 * @return void
	 */
	public function wc_admin_notice() {

	    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Subscribe All the Things requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', self::TEXT_DOMAIN ), self::REQ_WC_VERSION ) . '</p></div>';
	}

	/**
	 * Display a warning message if WC version check fails.
	 *
	 * @return void
	 */
	public function wcs_admin_notice() {

	    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Subscribe All the Things requires WooCommerce Subscriptions version 2.0+.', self::TEXT_DOMAIN ), self::REQ_WC_VERSION ) . '</p></div>';
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function init_textdomain() {

		load_plugin_textdomain( 'woocommerce-subscribe-all-the-things', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Store plugin version.
	 *
	 * @return void
	 */
	public function activate() {

		global $wpdb;

		$version = get_option( 'wcsatt_version', false );

		if ( $version === false ) {
			add_option( 'wcsatt_version', self::VERSION );
		} elseif ( version_compare( $version, self::VERSION, '<' ) ) {
			update_option( 'wcsatt_version', self::VERSION );
		}
	}

	/**
	 * Deactivate extension.
	 *
	 * @return void
	 */
	public function deactivate() {

		delete_option( 'wcsatt_version' );
	}

	/**
	 * Product types supported by the plugin.
	 * You can dynamically attach subscriptions to these product types
	 *
	 * @return array
	 */
	public function get_supported_product_types() {

		return apply_filters( 'wcsatt_supported_product_types', array( 'simple', 'variation', 'mix-and-match', 'bundle', 'composite' ) );
	}

}

endif; // end class_exists check

/**
 * Returns the main instance of WCS_ATT to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return WooCommerce Cart Subscriptions
 */
function WCS_ATT() {

  return WCS_ATT::instance();
}

// Launch the whole plugin
$GLOBALS[ 'woocommerce_subscriptions_cart' ] = WCS_ATT();
