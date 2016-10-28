<?php
add_filter( 'user_has_cap', 'jt_user_has_cap_filter', 10, 4 );

function jt_user_has_cap_filter( $allcaps, $caps, $args, $user ) {

    if ( ! is_singular( 'sqms_payne_dealer' ) )
        return $allcaps;

    // This is meta from a dropdown of CPT that is added to user profield via CMB2
    $dealer_slug = get_user_meta( get_current_user_id(), 'sqms-product-dealer-id', true );

    if ( $dealer_slug ) {

        // This is the only (ugly ass) way I found to get the CPT ID via the page slug
        $page_path = 'sqms_payne_dealer/' . $dealer_slug;
        $dealer_page = get_page_by_path( basename( untrailingslashit( $page_path ) ), OBJECT, 'sqms_payne_dealer');

        if ( $dealer_page->ID === get_queried_object_id() ) {

            $needed_caps = array( 'gravityforms_view_entry_notes', 'gravityview_add_entry_notes', 'gravityview_view_entry_notes', 'gravityforms_edit_entry_notes' );

            foreach ( $needed_caps as $cap )
                $allcaps[ $cap ] = true;
        }
    }

    return $allcaps;
}
