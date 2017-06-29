<?php
/**
 * Functions for outputting and supporting the shortcodes.
 */

add_action( 'init', 'display_dealer_entries_register_shortcode' );
add_action( 'init', 'display_quote_table_register_shortcode' );
add_action( 'init', 'display_dealer_reviews_register_shortcode' );
add_action( 'wp_ajax_nopriv_gf_button_get_form', 'gf_button_ajax_get_form' );
add_action( 'wp_ajax_gf_button_get_form', 'gf_button_ajax_get_form' );

add_action( 'wp_ajax_nopriv_button_build_csv_export', 'build_csv_export' );
add_action( 'wp_ajax_button_build_csv_export', 'build_csv_export' );

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
	$report_form_id 	= 15;
	$search_criteria = array();
	$sorting         = array();
	$paging          = array( 'offset' => 0, 'page_size' => 250 );
	$total_count     = 0;

	$entries         = GFAPI::get_entries( $quote_form_id, $search_criteria, $sorting, $paging, $total_count );

	$csv_form_params = array(
		'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
		);

	wp_localize_script( 'load-report-form', 'csv_form_params', $csv_form_params );
	wp_enqueue_script( 'load-report-form' );


	$lead_list  = '';
	$lead_list  = '<h3 style="float:left;">Total Entries: '.$total_count.'</h3>';
	$lead_list .= '<table class="all-entry-list">';
	$lead_list .= '<button style="float:right;" id="list-export" class="export-entry-list">Export</button>';
	$lead_list .= '<thead>';
	$lead_list .= '<tr>';
	$lead_list .= '<th>Quote ID</th><th>Quote Date</th><th>Client Name</th><th>System</th><th>MSRP</th><th>Dealer</th><th>Reported</th>';
	$lead_list .= '</tr>';
	$lead_list .= '</thead>';
	$lead_list .= '<tbody>';

	foreach ($entries as $entry ) {
		$is_reported 				= false;
		$prod_obj 					= get_page_by_path($entry['56'], OBJECT, 'sqms_prod_select');
		$product_post_id 			= $prod_obj->ID;
		$quoted_price 				= get_post_meta( $product_post_id, 'sqms-product-system-price', true );
		$dealer_name 			= get_the_title( $entry['69'] );
		$client_name 				= $entry['11.3'] . ' ' . $entry['11.6'];
		$date 						= date_create( $entry['date_created']);
		$reported 					= gform_get_meta( intval( $entry['id'] ), 'quote_reported' );
		if( $reported === 'Yes' ) {
			$is_reported = true;
			$report_search['field_filters'][] 	= array( 'key' => '1', 'value' => $entry['id'] );
			$report_entry				= GFAPI::get_entries( $report_form_id, $report_search );

			$result = $report_entry[0][2];
			$upsell = $report_entry[0][3];
			$notes = $report_entry[0][4];
			$comments = $report_entry[0][5];
			$actual_sell = $report_entry[0][8];
			$actual_labor = $report_entry[0][9];

			$data = '<table><thead><tr><th>MSRP</th><th>Result</th><th>Actual Sale Price</th><th>Actual Labor Price</th><th>Upsell</th><th>Notes</th><th>Comments</th></tr></thead><tbody>';
			$data .= '<tr><td> ' . $quoted_price . ' </td><td> ' . $result . ' </td><td> ' . $actual_sell . ' </td><td> ' . $actual_labor . ' </td><td> ' . $upsell . ' </td><td> ' . $notes . ' </td><td> ' . $comments . ' </td></tr>';
			$data .= '</tbody></table>';

		}



		$lead_list .= '<tr class="'.( $is_reported ? "reported" : "" ).'">';
		$lead_list .= '<td>' . $entry['id'] . '</td><td>' . date_format( $date, 'F j, Y') . '</td><td>' . $client_name . '</td><td>' . $entry['56'] . '</td><td>' . $quoted_price . '</td><td>' . $dealer_name . '</td><td>' . ( $is_reported ? 'Yes <button class="show-report" data-report_data=" ' . $data .'">More Info</button>' : 'No'  ). '</td>';
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


function build_csv_export() {
	// output headers so that the file is downloaded rather than displayed
	// header('Content-type: text/csv');
	// header('Content-Disposition: attachment; filename="demo.csv"');

	// // do not cache the file
	// header('Pragma: no-cache');
	// header('Expires: 0');

	$upload_dir = wp_upload_dir();

	$csv_path = trailingslashit( $upload_dir['basedir'] );
	$filename="export-list_".date('c').".csv";
	$csv_filename = $csv_path . $filename;


	// create a file pointer connected to the output stream
	$file = fopen($csv_filename, 'w');

	$quote_form_id 	= 12;
	$report_form_id 	= 15;
	$search_criteria = array();
	$sorting         = array();
	$paging          = array( 'offset' => 0, 'page_size' => 250 );
	$total_count     = 0;

	$entries         = GFAPI::get_entries( $quote_form_id, $search_criteria, $sorting, $paging, $total_count );

	// Headers
	$csv_headers = array(
			'Quote ID',
			'Quote Date',
			'Client Name',
			'System',
			'MSRP',
			'Dealer',
			'Reported',
			'MSRP',
			'Result',
			'Actual Sell Price',
			'Actual Labor Price',
			'Upsell',
			'Notes',
			'Comments',
		);

	// send the column headers
	fputcsv( $file, $csv_headers );

	foreach ($entries as $entry ) {
		$prod_obj 					= get_page_by_path($entry['56'], OBJECT, 'sqms_prod_select');
		$product_post_id 			= $prod_obj->ID;
		$quoted_price 				= get_post_meta( $product_post_id, 'sqms-product-system-price', true );
		$dealer_name 			= get_the_title( $entry['69'] );
		$client_name 				= $entry['11.3'] . ' ' . $entry['11.6'];
		$date 						= date_create( $entry['date_created']);
		$reported 					= gform_get_meta( intval( $entry['id'] ), 'quote_reported' );
		$is_reported 				= false;

		if( $reported === 'Yes' ) {
			error_log('REPORTED!!');
			$is_reported = true;
			$csv_report_search['field_filters'][] 	= array( 'key' => '1', 'value' => $entry['id'] );
			$csv_report_entry				= GFAPI::get_entries( $report_form_id, $csv_report_search );

			$result = $csv_report_entry[0][2];
			$upsell = $csv_report_entry[0][3];
			$notes = $csv_report_entry[0][4];
			$comments = $csv_report_entry[0][5];
			$actual_sell = $csv_report_entry[0][8];
			$actual_labor = $csv_report_entry[0][9];

			$report_data = array(
					$quoted_price,
					$result,
					$actual_sell,
					$actual_labor,
					$upsell,
					$notes,
					$comments,
				);

			// error_log('REPORT DATA:');
			// error_log( print_r( $report_data, true ) );
		}

		$row = array(
				$entry['id'],
				date_format( $date, 'F j, Y'),
				$client_name,
				$entry['56'],
				$quoted_price,
				$dealer_name,
				$reported,
			);

		$result = $row;

		if( $is_reported ) {
			$result = array_merge( $row, $report_data );
			// error_log('Result:');
			// error_log( print_r( $result, true ) );
		}

		fputcsv( $file, $result );
	}
	// Close the file
	fclose($file);

	$csv_location = trailingslashit( content_url() ) . 'uploads/' . $filename;
	echo $csv_location;
	die();
}
