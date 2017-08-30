<?php
/**
 * Handle loading the zip code check form via shortcode and outputting either
 * an error message or the system selector form.
 */

add_action( 'init', 'check_zip_code_register_shortcode' );
add_action( 'wp_ajax_nopriv_get_gravity_form', 'gf_zip_load_form' );
add_action( 'wp_ajax_get_gravity_form', 'gf_zip_load_form' );

/**
 * Register shortocde to output the pre-form zip code check form
 * @return void registers shortcode
 */
function check_zip_code_register_shortcode() {
	add_shortcode( 'check-zip-code-form', 'get_check_zip_form' );
}

/**
 * Function to build the zip code check form output
 * @return string HTML for zip code check form
 */
function get_check_zip_form() {
	$selection_form_id = 12;
	$market_zone = 'PHX-';

	// spanish_check
	/**
	 * Enqueue the scripts and styles for selection form here to get them
	 * into the page because the form itself is loaded in via ajax.
	 */
	gravity_form_enqueue_scripts( $selection_form_id, true );

	if( isset( $_GET['dealer_ref'] ) && is_valid( $_GET['dealer_ref'] ) ) {
		gravity_form( $selection_form_id, false, false, false, array('market_key'=>$market_zone), true );
	}
	elseif( isset( $_GET['spanish'] ) ) {
		gravity_form( $selection_form_id, false, false, false, array('market_key'=>$market_zone, 'spanish_check'=>'yes'), true );
	}
	else {
		$zip_form_params = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			);

		wp_localize_script( 'load-zip-form', 'zip_form_params', $zip_form_params );
		wp_enqueue_script('load-zip-form');

		// Pretty much need JS enabled for all this to work right, so send a message if not
		$no_script_message = '<noscript><div class="avia_message_box avia-color-red avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last  "><span class="avia_message_box_title">Enable JavaScript!</span><div class="avia_message_box_content"><p style="text-transform:none;font-size:16px;">For full functionality of this site it is necessary to enable JavaScript. Here are the <a href="http://www.enable-javascript.com/" target="_blank"> instructions how to enable JavaScript in your web browser</a>.</p></div></div></noscript>';

		$check_zip_form = '<h2>Let\'s Get Started with your Free, NO OBLIGATION HVAC System Quote</h2><form action="" id="get_gravity_form" class="sqms-zip-search-form clearfix"><p class="zip-input-wrap"><label>Enter Your Zip Code in the box below and CLICK "Go To Next Step"<input name="zip_code_input" class="text_input" type="text" id="zip-check-input" value="" placeholder="enter zip code here..."></label></p><p class="zip-button-wrap"><input type="submit"  value="Go To The Next Step" class="gf-test-zip-code button"></p></form>';

		return $no_script_message . $check_zip_form;
	}
}

/**
 * The ajax function called from the zip code check form, it comfirms a valid zip code
 * and either gives back the selection form or aborts with error messages
 * @return void Embeds GF selection form or echo error
 */
function gf_zip_load_form(){

	// File that maps the serviceable zip codes to their market zones
	include 'data/data-markets.php';

	$zip_zone = '';
	$zip_form_id = 12;

	// Do we have a zip filled out and is it valid?
	if ( isset($_GET['zip_value']) && is_us_zip_code($_GET['zip_value']) ) {

		$zip_entered 	= $_GET['zip_value'];
		$zip_term		= get_term_by( 'slug', $zip_entered, 'zone' );
		$zip_parent 	= get_term_by( 'id', $zip_term->parent, 'zone' );

		// Check if we found the entered zip code matching a zone
		if( $zip_parent ) {

			$zip_zone = $zip_parent->slug;

			foreach ($market_list as $market => $zone) {

				if( in_array( $zip_zone, $zone['zones'] ) ) {
					$market_zone = $zone['label'];
					break;
				}
			}
			// All good, output the GF selection form!
			gravity_form( $zip_form_id, false, false, false, array('market_key'=>$market_zone), true );
		}
		else {
		// No matching zip area found, send sorry message
		echo '<div class="avia_message_box avia-color-blue avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last  "><span class="avia_message_box_title">Check Back Soon!</span><div class="avia_message_box_content"><p style="text-transform:none;font-size:16px;">HVAC IQ currently services the greater Phoenix, AZ market. We are expanding throughout the southwest, check back soon to see if we service your city!</p></div></div>';
		}
	}
	else {
	// Failed validation!
	echo '<div class="avia_message_box avia-color-red avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last  "><span class="avia_message_box_title">Invalid Zip Code!</span><div class="avia_message_box_content"><p style="text-transform:none;font-size:16px;">Please enter a valid US Zip Code</p></div></div><input type="button" class="zip-error-refresh button" value="Try Again?" onClick="window.location.reload()">';die();
	}
	die();
}

/**
 * Checks for valid zip code
 * @param  int  $zip_code The zip code entered by user
 * @return bool           pass or fail
 */
function is_us_zip_code($zip_code) {
	// Make sure there IS a zip entered, that it is proper length, and it matches US format
	if ( empty($zip_code) || strlen(trim($zip_code)) > 5 || !preg_match('/^\d{5}$/', $zip_code) ) {
		return false;
	}
	return true;
}
