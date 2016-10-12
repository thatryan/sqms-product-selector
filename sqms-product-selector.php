<?php
/*
	Plugin Name: SQMS Product Selector
	Plugin URI: https://github.com/thatryan
	Description: Register product and handle logic for chooser
	Author: Ryan Olson
	Version: 1.0.12
	Author URI: http://thatryan.com
 */

add_action( 'plugins_loaded', array ( Product_Selector::get_instance(), 'plugin_setup' ) );

class Product_Selector {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;


	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	public $plugin_version = '1.0.12';


	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';


	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   1.0.0
	 * @return  object of this class
	 */
	public static function get_instance() {

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;

	}


	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @since   1.0.0
	 * @return  void
	 */
	public function plugin_setup() {

		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );

		define( 'SQMS_PLUGIN_NAME', 'sqms-product-selector' );
		define( 'SQMS_PLUGIN_NAME_PLUGIN', 'sqms-product-selector/sqms-product-selector.php' );
		define( 'SQMS_PROD_SEL_PATH', WP_PLUGIN_DIR . '/' . SQMS_PLUGIN_NAME );
		define( 'SQMS_PROD_SEL_URL', WP_PLUGIN_URL . '/' . SQMS_PLUGIN_NAME );

		define('YELP_CONSUMER_KEY', 'VIPD3IeOb4UFDyVZ5Ya7sg');
		define('YELP_CONSUMER_SECRET', 'J1VBNuDIyyW55i7MbvAbs_Q0X3E');
		define('YELP_TOKEN', 'Uf2eTzI4L4pl6vmQQD2xBMAIUex-UhC6');
		define('YELP_TOKEN_SECRET', 'uwcT8oNwrpsL47XWNcmsZCsQvaA');
		define('YELP_UNSIGNED_URL', "http://api.yelp.com/v2/business/");
		define('GMAP_API_KEY', "AIzaSyCf10fd47UiQeQNY1joeeFKdTgT2FOXNiY");

		require_once( 'includes/post-type.php' );

		require_once( 'cmb2/init.php' );
		require_once( 'includes/meta.php' );

		require_once( 'includes/gravity-forms-functions.php' );
		require_once( 'includes/shortcode.php' );

		add_filter( 'the_content', array ( $this, 'get_custom_post_type_template' ) );

		add_filter( 'login_redirect', array( $this, 'dealer_login_redirect' ), 10, 3 );
		add_filter('avf_builder_boxes', 'add_builder_to_posttype');

	}

	public function add_builder_to_posttype($metabox)
	{
		foreach($metabox as &$meta)
		{
			if($meta['id'] == 'avia_builder' || $meta['id'] == 'layout')
			{
				$meta['page'][] = 'sqms_payne_dealer';
			}
		}

		return $metabox;
	}

	public function get_custom_post_type_template($content) {

		     if( is_singular( 'sqms_prod_select' ) && is_main_query() ) {
		     	$single_template = dirname( __FILE__ ) . '/includes/cpt-template.php';
		     		$content = include $single_template;
		     	}
		     	return $content;
	}


	public function dealer_login_redirect( $redirect_to, $request, $user  ) {

		if( $user->user_login == 'preview' ) {
			return home_url();
		}
		//is there a user to check?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {

			//check for dealers
			if ( in_array( 'subscriber', $user->roles ) ) {
				// redirect them to the dealer leads list
				return get_permalink( 138 );
			} else {
				return $redirect_to;
			}
		} else {
			return $redirect_to;
		}
	}

	/**
	 * Constructor. Intentionally left empty and public.
	 *
	 * @see plugin_setup()
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'header_assets' ) );
	}


	public function header_assets() {
		wp_register_style( 'vex', $this->plugin_url . 'assets/css/vex.css', array(), $this->plugin_version );
		wp_register_style( 'vex-theme', $this->plugin_url . 'assets/css/vex-theme-default.css', array(), $this->plugin_version );

		wp_register_script( 'vex-script', $this->plugin_url . 'assets/js/vex.combined.min.js', array ('jquery'), $this->plugin_version, false );
		wp_register_script( 'load-report-form-script', $this->plugin_url . 'assets/js/load-report-form.js', array ('jquery'), $this->plugin_version, false );
		wp_register_script( 'sqms-prod-select-script', $this->plugin_url . 'assets/js/sqms-product-selector.js', array ('jquery'), $this->plugin_version, false );

		wp_enqueue_style( 'sqms-prod-select', $this->plugin_url . 'assets/css/sqms-product-selector.css', array(), $this->plugin_version );
		wp_enqueue_style( 'vex' );
		wp_enqueue_style( 'vex-theme' );
		// wp_enqueue_script('sqms-prod-select-script');
		wp_enqueue_script('vex-script');
	}

	/**
	 * Run install related functionality
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function install() {

		require_once( 'includes/post-type.php' );
		sqms_register_productselector_post_type();
		sqms_register_productselector_tax();

		flush_rewrite_rules();

	}


	/**
	 * Handles uninstalling plugin
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function uninstall() {

		$productselectors = get_posts( array(
			'post_type'      => 'sqms_prod_select',
			'posts_per_page' => -1,
		) );

		foreach( $productselectors as $login ) {

			 wp_delete_post( $login->ID, true);

		}

		flush_rewrite_rules();

	}

}

register_activation_hook( __FILE__, array( 'Product_Selector', 'install' ) );
register_uninstall_hook( __FILE__, array( 'Product_Selector', 'uninstall' ) );
