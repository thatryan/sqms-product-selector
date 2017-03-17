<?php
// add_action( 'template_redirect', 'dealer_caps' );

function dealer_caps() {
	$data = get_userdata( get_current_user_id() );
	if ( is_object( $data) ) {
	    $current_user_caps = $data->allcaps;
	     echo '<pre>' . print_r( $current_user_caps, true ) . '</pre>';
	}
}


add_filter( 'map_meta_cap', 'jt_map_meta_cap', 10, 4 );

function jt_map_meta_cap( $caps, $cap, $user_id, $args ) {
echo '<pre>';
print_r($caps);
echo '</pre>';
exit();
    // GF or GV should have some meta capability for viewing, editing, etc.
    // There may be multiple caps to check for.  For now, we'll just focus on
    // one.  Basically, you need to see if this is the meta cap being checked
    // for before running your code.
    if ( 'gravityforms_edit_entry_notes' === $cap ) {

        // $args[0] is generally where the object ID (post ID in the case
        // of posts) is passed in for this particular meta cap check.
        if ( is_array( $args ) && isset( $args[0] ) ) {

            // OK.  So, we have post ID at this point.  Let's get the
            // "dealer ID", which is stored as post meta.
            $dealer_id = get_post_meta( absint( $args[0] ), 'sqms-product-dealer-id', true );

            // Now we need to check if the dealer ID matches the user ID
            // (current user).  If so, we need to "map" some primitive caps
            // to this meta cap.  It can be anything as simple as the "read"
            // capability.  Or, it can be multiple capabilities.  We know the
            // current user is the dealer, so we'll just keep it simple and
            // set it to "read".
            if ( absint( $dealer_id ) === absint( $user_id ) ) {

                $caps = array( 'gravityview_add_entry_notes' );
            }
        }
    }

    // Always return the array of caps.
    return $caps;
}

/**
 * Set capabilities so that logged in dealers, who are viewing their own page, can reply
 * to reviews, which are Gravity View notes.
 */
// add_filter( 'map_meta_cap', 'sqms_user_has_cap_filter', 10, 4 );


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
