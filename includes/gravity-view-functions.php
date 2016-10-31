<?php
add_filter( 'gravityview_field_entry_value_survey', 'get_stars', 10, 4 );

function get_stars( $output, $entry, $field_settings, $current_field ) {

	$valid_values = array( '1', '2', '3', '4', '5' );

	if( !in_array( $output, $valid_values ) ) {

		$output = trim(strip_tags($output, 'h4'));
	}

	$args = array(
	   'rating' => $output,
	   'type' => 'rating',
	   'number' => 1,
	   'echo' => false,
	);

	$rating_stars = wp_star_rating($args);

	return $rating_stars;

}

add_filter( 'gravitview_no_entries_text', 'gv_no_dealers', 10, 4 );

function gv_no_dealers( $output, $is_search ) {
	$dealer_id = get_the_ID();

	return 'No reviews yet! Would you like to <a href="https://hvacinstantquote.com/submit-dealer-review/?dealer_id='.$dealer_id.'" target="_blank">leave a review</a> for this dealer?';
}
