<?php
/**
 * Functions for outputting and supporting the shortcodes.
 */

add_action( 'init', 'display_dealer_entries_register_shortcode' );
add_action( 'init', 'display_quote_table_register_shortcode' );
add_action( 'init', 'display_dealer_reviews_register_shortcode' );
add_action( 'wp_ajax_nopriv_gf_button_get_form', 'gf_button_ajax_get_form' );
add_action( 'wp_ajax_gf_button_get_form', 'gf_button_ajax_get_form' );

/**
 * Add shortcode to output list of dealer entries.
 * @return void Registers shortcode
 */
function display_dealer_entries_register_shortcode() {
	add_shortcode( 'dealer-entries', 'display_dealer_entries' );
}

function display_quote_table_register_shortcode() {
	add_shortcode( 'quote-table', 'display_quote_table' );
}

/**
 * Build the list of entries for the logged in dealer, including report form.
 * @return string HTML strucutre of table list
 */
function display_dealer_entries() {

	$report_form_id 	= 15;
	$quote_form_id 	= 12;
	$current_user 		= wp_get_current_user();
	$dealer_slug 		= get_user_meta( $current_user->ID, 'sqms-product-dealer-id', true );
	$page_path 		= 'sqms_payne_dealer/' . $dealer_slug;
	$dealer_page 		= get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');
	$dealer_id 			= $dealer_page->ID;
	$dealer_name 	= get_the_title( $dealer_id );

	$search_criteria['field_filters'][] 	= array( 'key' => '69', 'value' => $dealer_id );
	$entries 							= GFAPI::get_entries( $quote_form_id, $search_criteria );

	if( !$entries ) {
		$no_leads = '<h3>Sorry, no leads yet!</h3>';
		return $no_leads;
	}

	// Enqueue the scripts and styles for report form
	gravity_form_enqueue_scripts( $report_form_id, true );

	$form_params = array(
		'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
		'report_form_id' 	=> $report_form_id,
		'dealer_slug' 		=> $dealer_slug,
		'dealer_name' 		=> $dealer_name,
		'dealer_id' 			=> $dealer_id,
		);

	wp_localize_script( 'load-report-form', 'form_params', $form_params );
	wp_enqueue_script( 'load-report-form' );

	$lead_list  = '';
	$lead_list .= '<h3>Lead List For: '.$dealer_name.'</h3>';
	$lead_list .= '<table>';
	$lead_list .= '<thead>';
	$lead_list .= '<tr>';
	$lead_list .= '<th>Quote ID</th><th>Quote Date</th><th>Client Name</th><th>System</th><th>Reported</th>';
	$lead_list .= '</tr>';
	$lead_list .= '</thead>';
	$lead_list .= '<tbody>';

	foreach ($entries as $entry ) {

		$prod_obj 					= get_page_by_path($entry['56'], OBJECT, 'sqms_prod_select');
		$product_post_id 			= $prod_obj->ID;
		$quoted_price 				= get_post_meta( $product_post_id, 'sqms-product-system-price', true );
		$client_name 				= $entry['11.3'] . ' ' . $entry['11.6'];
		$client_email 				= $entry['12'];
		$reported 					= gform_get_meta( intval( $entry['id'] ), 'quote_reported' );
		$date = date_create( $entry['date_created']);

		$lead_list .= '<tr>';
		$lead_list .= '<td>' . $entry['id'] . '</td><td>' . date_format( $date, 'F j, Y') . '</td><td>' . $client_name . '</td><td>' . $entry['56'] . '</td><td>' . ( $reported === "Yes" ? $reported : '<button id="gf_button_get_form_' . $entry['id'] . ' " class="open-report-form" data-client_email=" ' . $client_email .'" data-entryid=" ' . $entry['id'] .' " data-quotecost=" ' . $quoted_price.' ">Report</button>'  ). '</td>';
		$lead_list .= '</tr>';
	}

	$lead_list .= '</tbody>';
	$lead_list .= '</table>';
	$lead_list .= '<div id="gf_button_form_container" style="display:none;"></div>';

	return $lead_list;
}


function display_quote_table() {

	$quote_form_id 	= 12;
	$search_criteria = array();
	$sorting         = array();
	$paging          = array( 'offset' => 0, 'page_size' => 250 );
	$total_count     = 0;

	$entries         = GFAPI::get_entries( $quote_form_id, $search_criteria, $sorting, $paging, $total_count );


	$lead_list  = '';
	$lead_list  = '<h3>Total Entries: '.$total_count.'</h3>';
	$lead_list .= '<table class="all-entry-list">';
	$lead_list .= '<thead>';
	$lead_list .= '<tr>';
	$lead_list .= '<th>Quote Date</th><th>Client Name</th><th>System</th><th>Dealer</th><th>Reported</th>';
	$lead_list .= '</tr>';
	$lead_list .= '</thead>';
	$lead_list .= '<tbody>';

	foreach ($entries as $entry ) {

		$prod_obj 					= get_page_by_path($entry['56'], OBJECT, 'sqms_prod_select');
		$product_post_id 			= $prod_obj->ID;
		$dealer_name 			= get_the_title( $entry['69'] );
		$client_name 				= $entry['11.3'] . ' ' . $entry['11.6'];

		$reported 					= gform_get_meta( intval( $entry['id'] ), 'quote_reported' );
		$date = date_create( $entry['date_created']);


		$lead_list .= '<tr class="'.($reported === 'Yes' ? "reported" : "" ).'">';
		$lead_list .= '<td>' . date_format( $date, 'F j, Y') . '</td><td>' . $client_name . '</td><td>' . $entry['56'] . '</td><td>' . $dealer_name . '</td><td>' . ( $reported === "Yes" ? $reported : 'No'  ). '</td>';
		$lead_list .= '</tr>';
	}

	$lead_list .= '</tbody>';
	$lead_list .= '</table>';

	return $lead_list;
}


/**
 * Add shortcode to display dealer ratings
 * @return void registers shortcode
 */
function display_dealer_reviews_register_shortcode() {
	add_shortcode( 'dealer-reviews', 'display_dealer_reviews' );
}

/**
 * Output the dealer review rating
 * @return string HTML for stars
 */
function display_dealer_reviews() {

	$dealer_id 			= get_the_ID();
	$review_form_id 	= 20;
	$ratings 			= array();

	$search_criteria['field_filters'][] 	= array( 'key' => '3', 'value' => $dealer_id );
	$entries         						= GFAPI::get_entries( $review_form_id, $search_criteria );

	if( empty( $entries ) ) {
		return;
	}

	foreach ($entries as $entry ) {

		$ratings[] = $entry['1'];
	}

	$ratings_total 		= count($ratings);
	$average 			= array_sum($ratings) / $ratings_total;
	$rating_average 	= round($average, 1, PHP_ROUND_HALF_ODD);
	$args 				= array(
					   'rating' 	=> $rating_average,
					   'type' 	=> 'rating',
					   'number' 	=> $ratings_total,
					   'echo' 	=> false,
					);

	$rating_stars 		= wp_star_rating($args);


	return $rating_stars;
}

/**
 * Get the form to send a dealer report on the proper entry.
 * @return void Outputs Gravity Form via ajax
 */
function gf_button_ajax_get_form(){

	$report_form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

	gravity_form( $report_form_id,true, false, false, false, true );

	die();

}
