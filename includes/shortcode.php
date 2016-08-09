<?php

add_action( 'wp_ajax_nopriv_gf_button_get_form', 'gf_button_ajax_get_form' );
add_action( 'wp_ajax_gf_button_get_form', 'gf_button_ajax_get_form' );

function display_dealer_entries() {

	$report_form_id = 15;
	$quote_form_id = 12;
	// Enqueue the scripts and styles
	gravity_form_enqueue_scripts( $report_form_id, true );

	$current_user = wp_get_current_user();
	$dealer_id = get_user_meta( $current_user->ID, 'sqms-product-dealer-id', true );

	$page_path = 'sqms_payne_dealer/' . $dealer_id;
	$dealer_page = get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');

	$dealer_name = get_the_title( $dealer_page->ID );

	$search_criteria['field_filters'][] = array( 'key' => 'dealer', 'value' => $dealer_id );
	$entries         = GFAPI::get_entries( $quote_form_id, $search_criteria );

	$form_params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'report_form_id' => $report_form_id,
		'dealer_id' => $dealer_name,
		);

	wp_localize_script( 'load-report-form-script', 'form_params', $form_params );
	wp_enqueue_script( 'load-report-form-script' );

	$lead_list = '';
	$lead_list .= '<table>';
	$lead_list .= '<thead>';
	$lead_list .= '<tr>';
	$lead_list .= '<th>Quote ID</th><th>Quote Date</th><th>System</th><th>Reported</th>';
	$lead_list .= '</tr>';
	$lead_list .= '</thead>';
	$lead_list .= '<tbody>';

	foreach ($entries as $entry ) {
		$reported = gform_get_meta( intval( $entry['id'] ), 'quote_reported' );
		$lead_list .= '<tr>';
		$lead_list .= '<td>' . $entry['id'] . '</td><td>' . $entry['date_created'] . '</td><td>' . $entry['56'] . '</td><td>' . ( $reported === "Yes" ? $reported : '<button id="gf_button_get_form_' . $entry['id'] . ' " class="open-report-form" data-entryid="' . $entry['id'] . '">Report</button>'  ). '</td>';
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
