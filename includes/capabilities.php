<?php
// add_action( 'template_redirect', 'dealer_caps' );

function dealer_caps() {
	$data = get_userdata( get_current_user_id() );
	if ( is_object( $data) ) {
	    $current_user_caps = $data->allcaps;
	     echo '<pre>' . print_r( $current_user_caps, true ) . '</pre>';
	}
}




/**
 * Set capabilities so that logged in dealers, who are viewing their own page, can reply
 * to reviews, which are Gravity View notes.
 */
add_filter( 'map_meta_cap', 'sqms_user_has_cap_filter', 10, 4 );


function sqms_user_has_cap_filter( $allcaps, $cap, $user_id, $args ) {

	if ( ! did_action( 'template_redirect' ) ) {
		return $allcaps;
	}

	// This is meta from a dropdown of CPT that is added to user profield via CMB2
	$dealer_slug = get_user_meta( $user_id, 'sqms-product-dealer-id', true );

	if ( $dealer_slug ) {

		// This is the only (ugly ass) way I found to get the CPT ID via the page slug
		$page_path = 'sqms_payne_dealer/' . $dealer_slug;
		$dealer_page = get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');

		// Make sure the page being viewed belongs to the logged in dealer
		if ( $dealer_page->ID === get_queried_object_id() ) {
$allcaps = array();
			$needed_caps = array( 'gravityforms_view_entry_notes', 'gravityview_add_entry_notes', 'gravityview_view_entry_notes', 'gravityforms_edit_entry_notes' );

			foreach ( $needed_caps as $new_cap ) {
				$allcaps[ $new_cap ] = true;
			}
		}

	}

	return $allcaps;
}
