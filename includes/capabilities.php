<?php
/**
 * Set capabilities so that logged in dealers, who are viewing their own page, can reply
 * to reviews, which are Gravity View notes.
 */
add_filter( 'user_has_cap', 'sqms_user_has_cap_filter', 10, 4 );

/**
 * Add capabilites to logged in dealers so they can see/reply to "reviews" with GF entry notes.
 * @param  array $allcaps Current dealer's capabilities
 * @param  array $caps    Required cap to do this stuff
 * @param  array $args    Other arguments, not used here
 * @param  object $user    WP_User object
 * @return array          New capabilities set
 */
function sqms_user_has_cap_filter( $allcaps, $caps, $args, $user ) {

	// Only do this mess if the page being viewed is a dealer landing page
	if ( ! is_singular( 'sqms_payne_dealer' ) ) {
		return $allcaps;
	}

	// This is meta from a dropdown of CPT that is added to user profield via CMB2
	$dealer_slug = get_user_meta( get_current_user_id(), 'sqms-product-dealer-id', true );

	if ( $dealer_slug ) {

		// This is the only (ugly ass) way I found to get the CPT ID via the page slug
		$page_path = 'sqms_payne_dealer/' . $dealer_slug;
		$dealer_page = get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');

		// Make sure the page being viewed belongs to the logged in dealer
		if ( $dealer_page->ID === get_queried_object_id() ) {

			$needed_caps = array( 'gravityforms_view_entry_notes', 'gravityview_add_entry_notes', 'gravityview_view_entry_notes', 'gravityforms_edit_entry_notes' );

			foreach ( $needed_caps as $cap ) {
				$allcaps[ $cap ] = true;
			}
		}
	}

	return $allcaps;
}
