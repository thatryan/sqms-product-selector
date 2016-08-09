<?php

add_action( 'cmb2_init', 'sqms_prodcut_selector_meta' );

function sqms_prodcut_selector_meta() {
	// Prefix the meta
	$prefix = 'sqms-product-';

	/**
	 * Metabox for the user profile screen
	 */
	$cmb_user = new_cmb2_box( array(
		'id'               => $prefix . 'dealer-info',
		'title'            => __( 'User Profile Metabox', 'cmb2' ), // Doesn't output for user boxes
		'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta
		'show_names'       => true,
		'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
	) );

	$cmb_user->add_field( array(
		'name'    => __( 'Dealer ID', 'cmb2' ),
		'desc'    => __( 'CPT id associated with this dealer', 'cmb2' ),
		'id'      => $prefix . 'dealer-id',
		'type'    => 'select',
		'options_cb' => 'cmb2_get_your_post_type_post_options',
	) );

	// Create metabox container for dealers
	$sqms_dealer_meta = new_cmb2_box( array(
		'id'            => $prefix . 'dealer-meta',
		'title'         => __( 'Dealer Data', 'sqmsprodsel' ),
		'object_types'  => array( 'sqms_payne_dealer', ),
	) );

	$sqms_dealer_meta->add_field( array(
		'name'       => __( 'Address', 'sqmsprodsel' ),
		'id'         => $prefix . 'address',
		'type'       => 'address',
	) );

	$sqms_dealer_meta->add_field( array(
		'name'       => __( 'Map Location', 'sqmsprodsel' ),
		'id'         => $prefix . 'location',
		'type'       => 'pw_map',
		'split_values' => true,
	) );

	$sqms_dealer_meta->add_field( array(
		'name'       => __( 'Phone Number', 'sqmsprodsel' ),
		'id'         => $prefix . 'phone',
		'type'       => 'text',
	) );

	$sqms_dealer_meta->add_field( array(
		'name'       => __( 'Contact Email', 'sqmsprodsel' ),
		'id'         => $prefix . 'email',
		'type'       => 'text',
	) );

	$sqms_dealer_meta->add_field( array(
		'name'       => __( 'Yelp ID', 'sqmsprodsel' ),
		'id'         => $prefix . 'yelp',
		'type'       => 'text',
	) );

	// Create metabox container for system overview
	$sqms_prod_overview_meta = new_cmb2_box( array(
		'id'            => $prefix . 'overview-meta',
		'title'         => __( 'System Overview', 'sqmsprodsel' ),
		'object_types'  => array( 'sqms_prod_select', ),
	) );

	// Add fields to overview container
	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'AHRI', 'sqmsprodsel' ),
		'id'         => $prefix . 'ahri',
		'type'       => 'text',
		'default'       => 'NA',
		'column'       => true,
	) );
	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Tonnage', 'sqmsprodsel' ),
		'id'         => $prefix . 'tonnage',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'ODU Voltage', 'sqmsprodsel' ),
		'id'         => $prefix . 'odu-voltage',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'ODU Family', 'sqmsprodsel' ),
		'id'         => $prefix . 'odu-family',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Furnace Family', 'sqmsprodsel' ),
		'id'         => $prefix . 'furnace-family',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Application', 'sqmsprodsel' ),
		'id'         => $prefix . 'application',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Coil Family', 'sqmsprodsel' ),
		'id'         => $prefix . 'coil-family',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Coil Furnace Width', 'sqmsprodsel' ),
		'id'         => $prefix . 'coil-furnace-width',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Package Voltage', 'sqmsprodsel' ),
		'id'         => $prefix . 'package-voltage',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Package Model Number', 'sqmsprodsel' ),
		'id'         => $prefix . 'package-model-number',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Package Type', 'sqmsprodsel' ),
		'id'         => $prefix . 'package-type',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Package Configuration', 'sqmsprodsel' ),
		'id'         => $prefix . 'package-configuration',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Package Family', 'sqmsprodsel' ),
		'id'         => $prefix . 'package-family',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	// Create metabox container for system breakdown
	// $sqms_prod_breakdown_meta = new_cmb2_box( array(
	// 	'id'            => $prefix . 'breadkdown-meta',
	// 	'title'         => __( 'System Breakdown', 'sqmsprodsel' ),
	// 	'object_types'  => array( 'sqms_prod_select', ),
	// 	'closed'     => true,
	// ) );

	// Add fields to breakdown container
	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Model Version', 'sqmsprodsel' ),
		'id'         => $prefix . 'model-version',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'ODU Model', 'sqmsprodsel' ),
		'id'         => $prefix . 'odu-model',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'ODU Version', 'sqmsprodsel' ),
		'id'         => $prefix . 'odu-version',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Coil Model', 'sqmsprodsel' ),
		'id'         => $prefix . 'coil-model',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Coil Width', 'sqmsprodsel' ),
		'id'         => $prefix . 'coil-width',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Furnace Model', 'sqmsprodsel' ),
		'id'         => $prefix . 'furnace-model',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Furnace Width', 'sqmsprodsel' ),
		'id'         => $prefix . 'furnace-width',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Cooling BTU', 'sqmsprodsel' ),
		'id'         => $prefix . 'cooling-btu',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Cooling BTUH', 'sqmsprodsel' ),
		'id'         => $prefix . 'cooling-btuh',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'SEER', 'sqmsprodsel' ),
		'id'         => $prefix . 'seer',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'EER', 'sqmsprodsel' ),
		'id'         => $prefix . 'eer',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'High Heating 47f Capacity', 'sqmsprodsel' ),
		'id'         => $prefix . 'high-heating-47f-capacity',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'High Heating 47 F HSPF', 'sqmsprodsel' ),
		'id'         => $prefix . 'high-heating-47-f-hspf',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Low Heating 17f Capacity', 'sqmsprodsel' ),
		'id'         => $prefix . 'low-heating-17f-capacity',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'HSPF', 'sqmsprodsel' ),
		'id'         => $prefix . 'hspf',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Nominal Gas Heat BTUH', 'sqmsprodsel' ),
		'id'         => $prefix . 'nominal-gas-heat-btuh',
		'type'       => 'text',
		'default'       => 'NA',
	) );

	// Create metabox container for pricing
	// $sqms_prod_pricing_meta = new_cmb2_box( array(
	// 	'id'            => $prefix . 'cost-meta',
	// 	'title'         => __( 'System Pricing', 'sqmsprodsel' ),
	// 	'object_types'  => array( 'sqms_prod_select', ),
	// 	'context' => 'side',
	// 	'priority' => 'low',
	// ) );

	// Add fields to pricing container
	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'ODU Price', 'sqmsprodsel' ),
		'id'         => $prefix . 'odu-price',
		'type'       => 'text',
		'default'       => '0',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Coil Price', 'sqmsprodsel' ),
		'id'         => $prefix . 'coil-price',
		'type'       => 'text',
		'default'       => '0',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Furnace Price', 'sqmsprodsel' ),
		'id'         => $prefix . 'furnace-price',
		'type'       => 'text',
		'default'       => '0',
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'System Price', 'sqmsprodsel' ),
		'id'         => $prefix . 'system-price',
		'type'       => 'text',
		'default'       => '0',
		'column'       => true,
	) );

	$sqms_prod_overview_meta->add_field( array(
		'name'       => __( 'Package Unit Price', 'sqmsprodsel' ),
		'id'         => $prefix . 'package-unit-price',
		'type'       => 'text',
		'default'       => '0',
	) );
}


/**
 * Gets a number of posts and displays them as options
 * @param  array $query_args Optional. Overrides defaults.
 * @return array             An array of options that matches the CMB2 options array
 */
function cmb2_get_post_options( $query_args ) {

    $args = wp_parse_args( $query_args, array(
        'post_type'   => 'sqms_payne_dealer',
        'numberposts' => -1,
    ) );

    $posts = get_posts( $args );

    $post_options = array();
    if ( $posts ) {
        foreach ( $posts as $post ) {
          $post_options[ $post->post_name ] = $post->post_title;
        }
    }

    return $post_options;
}

/**
 * Gets 5 posts for your_post_type and displays them as options
 * @return array An array of options that matches the CMB2 options array
 */
function cmb2_get_your_post_type_post_options() {
    return cmb2_get_post_options( array( 'post_type' => 'sqms_payne_dealer', 'numberposts' => -1 ) );
}
