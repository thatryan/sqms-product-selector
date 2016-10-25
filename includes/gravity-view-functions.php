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
