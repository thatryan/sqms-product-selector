<?php
/*
	Plugin Name: SQMS Product Selector
	Plugin URI: https://github.com/thatryan
	Description: Register product and handle logic for chooser
	Author: Ryan Olson
	Version: 1.1.52
	Author URI: http://thatryan.com
 */

add_action( 'plugins_loaded', array ( Product_Selector::get_instance(), 'plugin_setup' ) );

class Product_Selector {

	/**
	 * Plugin instance.
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Current version of the plugin.
	 * @type string
	 */

	public $plugin_version = '1.1.51';

	/**
	 * Path to this plugin's directory.
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * Access this plugin’s working instance
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance() {

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;

	}

	/**
	 * Used for regular plugin work.
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function plugin_setup() {

		$this->plugin_url 		= plugins_url( '/', __FILE__ );
		$this->plugin_path 	= plugin_dir_path( __FILE__ );

		define( 'SQMS_PLUGIN_NAME', 'sqms-product-selector' );
		define( 'SQMS_PLUGIN_NAME_PLUGIN', 'sqms-product-selector/sqms-product-selector.php' );
		define( 'SQMS_PROD_SEL_PATH', WP_PLUGIN_DIR . '/' . SQMS_PLUGIN_NAME );
		define( 'SQMS_PROD_SEL_URL', WP_PLUGIN_URL . '/' . SQMS_PLUGIN_NAME );

		require_once( 'includes/post-type.php' );
		require_once( 'cmb2/init.php' );
		require_once( 'includes/meta.php' );
		require_once( ABSPATH . 'wp-admin/includes/template.php' );
		require_once( 'includes/gravity-forms-functions.php' );
		require_once( 'includes/gravity-view-functions.php' );
		require_once( 'includes/capabilities.php' );
		require_once( 'includes/shortcode.php' );
		require_once( 'includes/zip-code-load.php' );

		add_filter( 'login_redirect', array( $this, 'dealer_login_redirect' ), 10, 3 );

	}

	/**
	 * If a dealer is logging in, redirect them to the page
	 * showing their leads to report on.
	 * @param  string $redirect_to Redirect destination URL
	 * @param  string $request     Requested redirect destination URL
	 * @param  object $user        WP_User object
	 * @return string              The URL to redirec to
	 */
	public function dealer_login_redirect( $redirect_to, $request, $user  ) {

		// A temp user to bypass the coming soon page for demos
		if( $user->user_login == 'preview' ) {
			return home_url();
		}

		//is there a user to check?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {

			//check for dealers
			if ( in_array( 'subscriber', $user->roles ) ) {
				// redirect them to the dealer leads list
				return get_permalink( 138 );
				}
				else {
					// Not a dealer, abort
					return $redirect_to;
				}
			}
			else {
				return $redirect_to;
			}
	}

	/**
	 * Constructor.
	 * @see plugin_setup()
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'header_assets' ) );
	}

	/**
	 * Register and enqueue scripts and styles for this plugin
	 * @return void Scripts & styles added to header
	 */
	public function header_assets() {

		// Register styles
		wp_register_style( 'vex', $this->plugin_url . 'assets/css/vex.css', array(), $this->plugin_version );
		wp_register_style( 'vex-theme', $this->plugin_url . 'assets/css/vex-theme-default.css', array(), $this->plugin_version );

		// Register scripts
		wp_register_script( 'vex-script', $this->plugin_url . 'assets/js/vex.combined.min.js', array ('jquery'), $this->plugin_version, false );
		wp_register_script( 'load-report-form-script', $this->plugin_url . 'assets/js/load-report-form.js', array ('jquery'), $this->plugin_version, false );
		wp_register_script( 'load-zip-form', $this->plugin_url . 'assets/js/load-zip-form.js', array ('jquery'), $this->plugin_version, false );
		wp_register_script( 'jquery-validate', $this->plugin_url . 'assets/js/jquery.validate.min.js', array ('jquery'), $this->plugin_version, false );
		wp_register_script( 'sqms-prod-select-script', $this->plugin_url . 'assets/js/sqms-product-selector.js', array ('jquery'), $this->plugin_version, false );

		// Enqueue styles
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'sqms-prod-select', $this->plugin_url . 'assets/css/sqms-product-selector.css', array(), $this->plugin_version );
		wp_enqueue_style( 'vex' );
		wp_enqueue_style( 'vex-theme' );

		// Enqueue scripts
		wp_enqueue_script('sqms-prod-select-script');
		wp_enqueue_script('vex-script');

	}

	/**
	 * Run install related functionality
	 * @return void
	 */
	public function install() {

		// Get our custom post types and taxonomies
		require_once( 'includes/post-type.php' );

		// Permalinks! Refresh them
		flush_rewrite_rules();

	}

	/**
	 * Handles uninstalling plugin
	 * @return void
	 */
	public function uninstall() {

		$productselectors = get_posts( array(
			'post_type' => array( 'sqms_prod_select', 'sqms_payne_dealer' ),
			'posts_per_page' => -1,
		) );

		foreach( $productselectors as $post ) {
			 wp_delete_post( $post->ID, true);
		}

		flush_rewrite_rules();

	}

}

register_activation_hook( __FILE__, array( 'Product_Selector', 'install' ) );
register_uninstall_hook( __FILE__, array( 'Product_Selector', 'uninstall' ) );
