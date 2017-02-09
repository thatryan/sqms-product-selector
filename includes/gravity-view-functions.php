<?php
/**
 * Functions for Gravity View, handling the dealer reviews
 */

add_filter( 'gravityview_field_entry_value_survey', 'get_stars', 10, 4 );
add_filter( 'gravitview_no_entries_text', 'gv_no_dealers', 10, 4 );
add_filter( 'gform_replace_merge_tags', 'gv_grab_post_id',10, 7 );

/**
 * Use a WP function to build legit stars based on a rating number
 * @param  string $output         HTML value output
 * @param  array $entry          GF $entry array
 * @param  array $field_settings Gravity View field settings
 * @param  array $current_field  Current field
 * @return string                 HTML output for stars rating
 */
function get_stars( $output, $entry, $field_settings, $current_field ) {

	$valid_values = array( '1', '2', '3', '4', '5' );

	if( !in_array( $output, $valid_values ) ) {

		$output = trim(strip_tags($output, 'h4'));
	}

	$args = array(
	   'rating' 	=> $output,
	   'type' 	=> 'rating',
	   'number' 	=> 1,
	   'echo' 	=> false,
	);

	$rating_stars = wp_star_rating($args);

	return $rating_stars;

}

/**
 * Modify the text displayed when there are no reviews.
 * @param string $output The existing "No Entries" text
 * @param bool $is_search  Is the current page a search result, or just a multiple entries screen?
 */
function gv_no_dealers( $output, $is_search ) {

	$dealer_id = get_the_ID();

	return 'No reviews yet! Would you like to <a href="https://hvacinstantquote.com/submit-dealer-review/?dealer_id='.$dealer_id.'" target="_blank">leave a review</a> for this dealer?';
}

/**
 * Custom merge tag for Gravity View, filter and sort, to only show reviews entries
 * for the current dealer page.
 * @param  string $text       Current text where merge tag lives
 * @param  object $form       GF $form object
 * @param  object $entry      GF $entry object
 * @param  bool $url_encode encode urls?
 * @param  book $esc_html   encode html?
 * @param  bool $nl2br      new lines to breaks?
 * @param  string $format     How should value be formatted? Default HTML
 * @return   string           Current dealer page ID
 */
function gv_grab_post_id ( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

	$merge_tag = '{current_post_id}';

	if ( strpos( $text, $merge_tag ) === false ) {
		return $text;
	}

	return get_the_ID();
}
