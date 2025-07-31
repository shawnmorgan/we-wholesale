<?php
/*
	Plugin Name:	Wholesale Ordering
	Description:	This plugin enables Wholesale Ordering for variable products
	Plugin URI:		https://series5technology.com/
	Author:			Series 5 Technology
	Author URI:		https://series5technology.com/contact/
	Text Domain:	wholesale-ordering
	Requires PHP:	7.0 or above
	Version:		1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Wholesale_Ordering' ) ) :
	
/**
 * Main Wholesale_Ordering Class
 *
 * @class   Wholesale_Ordering
 * 
 * @version 1.0.0
 * @since   1.0.0
 * 
 */
class Wholesale_Ordering {
	
	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 * 
	 */
	public $version = '1.0.0';

	/**
	 * @var   Wholesale_Ordering The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Wholesale_Ordering Instance
	 *
	 * Ensures only one instance of Wholesale_Ordering is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  Wholesale_Ordering - Main instance
	 * 
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Wholesale_Ordering Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * includes.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function includes() {
		require_once( 'inc/wholesale-ordering-main.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function admin() {

		// Version update
		if ( get_option( 'wholesale_order_p_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * version_updated.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function version_updated() {
		update_option( 'wholesale_order_p_version', $this->version );
	}

}

endif;

if ( ! function_exists( 'Wholesale_Ordering' ) ) {
	/**
	 * Returns the main instance of Wholesale_Ordering to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @return  Wholesale_Ordering
	 */
	function Wholesale_Ordering() {
		return Wholesale_Ordering::instance();
	}

}

add_action( 'plugins_loaded', 'Wholesale_Ordering' );
