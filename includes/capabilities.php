<?php
add_filter( 'map_meta_cap', 'dealer_add_notes', 10, 4 );

function dealer_add_notes( $caps, $cap, $user_id, $args ) {

	// This is meta from a dropdown of CPT that is added to user profield via CMB2
	$dealer_slug = get_user_meta( $user_id, 'sqms-product-dealer-id', true );

	// This is the only (ugly ass) way I found to get the CPT ID via the page slug
	$page_path = 'sqms_payne_dealer/' . $dealer_slug;
	$dealer_page = get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');
	$dealer_page_id = $dealer_page->ID;

	// Need to get ID of current page to see if it matches the ID of the CPT grabbed above
	$current_page_id = get_queried_object_id();

	$needed_caps = array( 'gravityforms_view_entry_notes', 'gravityview_add_entry_notes', 'gravityview_view_entry_notes', 'gravityforms_edit_entry_notes' );

	if( $dealer_page_id == $current_page_id ) {
		$new_caps = array();

		foreach ($needed_caps as $new_cap) {
			$new_caps[] = $new_cap;
		}
	}

	return $caps;

}
