<?php

// https://www.gravityhelp.com/documentation/article/using-dynamic-population/
// Above link for parameters on dynamic population
// used for sending data from lead list to form for auto notification to get review

add_action( 'wp_ajax_nopriv_gf_button_get_form', 'gf_button_ajax_get_form' );
add_action( 'wp_ajax_gf_button_get_form', 'gf_button_ajax_get_form' );

function display_dealer_entries() {

	$report_form_id = 15;
	$quote_form_id = 12;
	// Enqueue the scripts and styles
	gravity_form_enqueue_scripts( $report_form_id, true );

	$current_user = wp_get_current_user();
	$dealer_slug = get_user_meta( $current_user->ID, 'sqms-product-dealer-id', true );

	$page_path = 'sqms_payne_dealer/' . $dealer_slug;
	$dealer_page = get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');

	$dealer_id = $dealer_page->ID;

	$dealer_name = get_the_title( $dealer_id );

	$search_criteria['field_filters'][] = array( 'key' => 'dealer', 'value' => $dealer_slug );
	$entries         = GFAPI::get_entries( $quote_form_id, $search_criteria );

	$form_params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'report_form_id' => $report_form_id,
		'dealer_slug' => $dealer_slug,
		'dealer_name' => $dealer_name,
		'dealer_id' => $dealer_id,
		);

	wp_localize_script( 'load-report-form-script', 'form_params', $form_params );
	wp_enqueue_script( 'load-report-form-script' );

	$lead_list = '';
	$lead_list .= '<table>';
	$lead_list .= '<thead>';
	$lead_list .= '<tr>';
	$lead_list .= '<th>Quote ID</th><th>Quote Date</th><th>Client Name</th><th>Client Address</th><th>System</th><th>Reported</th>';
	$lead_list .= '</tr>';
	$lead_list .= '</thead>';
	$lead_list .= '<tbody>';

	foreach ($entries as $entry ) {

		$prod_obj = get_page_by_path($entry['56'], OBJECT, 'sqms_prod_select');
		$product_post_id = $prod_obj->ID;
		$quoted_system_price = get_post_meta( $product_post_id, 'sqms-product-system-price', true );

		$client_name = $entry['11.3'] . ' ' . $entry['11.6'];
		$client_email = $entry['12'];

		$client_address_field = "47";
		$client_address_street = $entry[$client_address_field . '.1'];
		$client_address_street2 = $entry[$client_address_field . '.2'];
		$client_address_city = $entry[$client_address_field . '.3'];
		$client_address_state = $entry[$client_address_field . '.4'];
		$client_address_zip = $entry[$client_address_field . '.5'];

		$client_address = $client_address_street . ', ' . ( $client_address_street2 ? $client_address_street2 . ', ' : '') . $client_address_city . ', ' . $client_address_state . ' ' . $client_address_zip;

		$reported = gform_get_meta( intval( $entry['id'] ), 'quote_reported' );
		$lead_list .= '<tr>';
		$lead_list .= '<td>' . $entry['id'] . '</td><td>' . $entry['date_created'] . '</td><td>' . $client_name . '</td><td>' . $client_address . '</td><td>' . $entry['56'] . '</td><td>' . ( $reported === "Yes" ? $reported : '<button id="gf_button_get_form_' . $entry['id'] . ' " class="open-report-form" data-client_email=" ' . $client_email .'" data-entryid=" ' . $entry['id'] .' " data-quotecost=" ' . $quoted_system_price.' ">Report</button>'  ). '</td>';
		$lead_list .= '</tr>';
	}

	$lead_list .= '</tbody>';
	$lead_list .= '</table>';
	$lead_list .= '<div id="gf_button_form_container" style="display:none;"></div>';

	return $lead_list;
}

function display_dealer_entries_register_shortcode() {
	add_shortcode( 'dealer-entries', 'display_dealer_entries' );
}
add_action( 'init', 'display_dealer_entries_register_shortcode' );

function gf_button_ajax_get_form(){

	$report_form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
	gravity_form( $report_form_id,true, false, false, false, true );

	die();
}


function display_dealer_reviews_register_shortcode() {
	add_shortcode( 'dealer-reviews', 'display_dealer_reviews' );
}
add_action( 'init', 'display_dealer_reviews_register_shortcode' );

function display_dealer_reviews() {
	$dealer_id = get_the_ID();
	$review_form_id = 20;
	$ratings = array();

	$search_criteria['field_filters'][] = array( 'key' => '3', 'value' => $dealer_id );
	$entries         = GFAPI::get_entries( $review_form_id, $search_criteria );


	foreach ($entries as $entry ) {

		$ratings[] = $entry['1'];
	}

	$ratings_total = count($ratings);
	$average = array_sum($ratings) / $ratings_total;
	$rating_average = round($average, 1, PHP_ROUND_HALF_ODD);
	$args = array(
	   'rating' => $rating_average,
	   'type' => 'rating',
	   'number' => $ratings_total,
	   'echo' => false,
	);

	$rating_stars = wp_star_rating($args);


	return $rating_stars;
}
