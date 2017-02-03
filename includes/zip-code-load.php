<?php

add_action( 'wp_ajax_nopriv_get_gravity_form', 'gf_zip_load_form' );
add_action( 'wp_ajax_get_gravity_form', 'gf_zip_load_form' );

add_action( 'init', 'check_zip_code_register_shortcode' );

function check_zip_code_register_shortcode() {
	add_shortcode( 'check-zip-code-form', 'get_check_zip_form' );
}

// [check-zip-code-form]
function get_check_zip_form() {

	$zip_form_params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);
	$zip_form_id = 12;

	// Enqueue the scripts and styles
	gravity_form_enqueue_scripts( $zip_form_id, true );

	wp_localize_script( 'load-zip-form', 'zip_form_params', $zip_form_params );
	wp_enqueue_script('jquery-validate');
	wp_enqueue_script('load-zip-form');

	// $check_zip_form = '<form action="" id="get_gravity_form" class="av-form-labels-visible avia-builder-el-0 avia-builder-el-no-sibling" ><p class=" first_form  form_element form_element_three_fourth av-last-visible-form-element" id="element_avia_1_1"><label for="zip_code_input">Please Enter Your Zip Code <abbr class="required" title="required">*</abbr></label><input name="zip_code_input" class="text_input is_empty" type="number" required id="zip-check-input" value=""></p><p class="form_element form_element_fourth modified_width"><input type="submit" value="Check Your Area" class="gf-test-zip-code button"></p></form>';
	$check_zip_form = '<form action="" id="get_gravity_form" class="sqms-zip-search-form clearfix"><p class="zip-input-wrap"><label>Please Enter Your Zip Code<input name="zip_code_input" class="text_input" type="text" id="zip-check-input" value="" placeholder="enter zip code..."></label></p><p class="zip-button-wrap"><input type="submit"  value="Check Your Area" class="gf-test-zip-code button"></p></form>';

	return $check_zip_form;
}

function gf_zip_load_form(){

	include 'data/data-markets.php';

	$zip_zone = '';
	$zip_form_id = 12;


	if ( isset($_GET['zip_value']) && is_us_zip_code($_GET['zip_value']) ) {

		$zip_entered = $_GET['zip_value'];
	    $zip_term = get_term_by( 'slug', $zip_entered, 'zone' );
	    $zip_parent = get_term_by( 'id', $zip_term->parent, 'zone' );

	    if( $zip_parent ) {
	    	$zip_zone = $zip_parent->slug;

	    	foreach ($market_list as $market => $zone) {

	    		if( in_array( $zip_zone, $zone['zones'] ) ) {
	    			$market_zone = $zone['label'];
	    			break;
	    		}
	    	}

	    	gravity_form( $zip_form_id, false, false, false, array('market_key'=>$market_zone), 1, true );
	    }
	    else {
	    	echo '<div class="avia_message_box avia-color-blue avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last  "><span class="avia_message_box_title">Check Back Soon!</span><div class="avia_message_box_content"><p style="text-transform:none;font-size:16px;">HVAC IQ currently services the greater Phoenix, AZ market. We are expanding throughout the southwest, check back soon to see if we service your city!</p></div></div>';
	    }
	}
	else {
		echo '<div class="avia_message_box avia-color-red avia-size-large avia-icon_select-yes avia-border-solid  avia-builder-el-1  el_after_av_textblock  avia-builder-el-last  "><span class="avia_message_box_title">Invalid Zip Code!</span><div class="avia_message_box_content"><p style="text-transform:none;font-size:16px;">Please enter a valid US Zip Code</p></div></div><input type="button" class="zip-error-refresh button" value="Try Again?" onClick="window.location.reload()">
';
		die();
	}

	die();
}

	function is_us_zip_code($zip_code) {
	    // scenario 1: empty
	    if ( empty($zip_code) || strlen(trim($zip_code)) > 5 || !preg_match('/^\d{5}$/', $zip_code) ) {
	        return false;
	    }

	    // passed successfully
	    return true;
	}
