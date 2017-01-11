<?php

add_action( 'init', 'sqms_register_productselector_post_type', 0 );
add_action( 'init', 'sqms_register_payne_dealer', 0 );
// add_action( 'init', 'sqms_register_dealer_zip', 0 );
add_action( 'init', 'sqms_register_dealer_zone', 0 );
add_filter('avf_builder_boxes', 'add_builder_to_posttype');
add_filter('avia_post_nav_entries','no_post_nav');

add_filter('manage_edit-sqms_payne_dealer_columns', 'ssla_add_id_column');
add_action('manage_sqms_payne_dealer_posts_custom_column', 'ssla_add_id_column_content', 10, 2);

// Register Products Post Type
function sqms_register_productselector_post_type() {

	$labels = array(
		'name'                  => _x( 'Payne Systems', 'Post Type General Name', 'sqms-prod-selector' ),
		'singular_name'         => _x( 'Payne System', 'Post Type Singular Name', 'sqms-prod-selector' ),
		'menu_name'             => __( 'Payne Systems', 'sqms-prod-selector' ),
		'name_admin_bar'        => __( 'Payne Systems', 'sqms-prod-selector' ),
		'archives'              => __( 'Payne System Archives', 'sqms-prod-selector' ),
		'parent_item_colon'     => __( 'Parent System:', 'sqms-prod-selector' ),
		'all_items'             => __( 'All Payne Systems', 'sqms-prod-selector' ),
		'add_new_item'          => __( 'Add New System', 'sqms-prod-selector' ),
		'add_new'               => __( 'Add New', 'sqms-prod-selector' ),
		'new_item'              => __( 'New Payne System', 'sqms-prod-selector' ),
		'edit_item'             => __( 'Edit System', 'sqms-prod-selector' ),
		'update_item'           => __( 'Update System', 'sqms-prod-selector' ),
		'view_item'             => __( 'View System', 'sqms-prod-selector' ),
		'search_items'          => __( 'Search Payne Systems', 'sqms-prod-selector' ),
		'not_found'             => __( 'Not found', 'sqms-prod-selector' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'sqms-prod-selector' ),
		'featured_image'        => __( 'Featured Image', 'sqms-prod-selector' ),
		'set_featured_image'    => __( 'Set featured image', 'sqms-prod-selector' ),
		'remove_featured_image' => __( 'Remove featured image', 'sqms-prod-selector' ),
		'use_featured_image'    => __( 'Use as featured image', 'sqms-prod-selector' ),
		'insert_into_item'      => __( 'Insert into item', 'sqms-prod-selector' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'sqms-prod-selector' ),
		'items_list'            => __( 'Items list', 'sqms-prod-selector' ),
		'items_list_navigation' => __( 'Items list navigation', 'sqms-prod-selector' ),
		'filter_items_list'     => __( 'Filter items list', 'sqms-prod-selector' ),
	);
	$rewrite = array(
		'slug'                  => 'payne-product',
		'with_front'            => false,
		'pages'                 => true,
		'feeds'                 => false,
	);
	$args = array(
		'label'                 => __( 'Payne System', 'sqms-prod-selector' ),
		'description'           => __( 'Post type for custom products in chooser', 'sqms-prod-selector' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'thumbnail'),
		'hierarchical'          => true,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-screenoptions',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => 'payne-products',
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'rewrite'               => $rewrite,
		'capability_type'       => 'page',
	);
	register_post_type( 'sqms_prod_select', $args );

}

// Register Dealer Post Type
function sqms_register_payne_dealer() {

	$labels = array(
		'name'                  => _x( 'Payne Dealers', 'Post Type General Name', 'sqms-prod-selector' ),
		'singular_name'         => _x( 'Payne Dealer', 'Post Type Singular Name', 'sqms-prod-selector' ),
		'menu_name'             => __( 'Payne Dealers', 'sqms-prod-selector' ),
		'name_admin_bar'        => __( 'Payne Dealers', 'sqms-prod-selector' ),
		'archives'              => __( 'Payne Dealer Archives', 'sqms-prod-selector' ),
		'parent_item_colon'     => __( 'Parent Item:', 'sqms-prod-selector' ),
		'all_items'             => __( 'All Payne Dealers', 'sqms-prod-selector' ),
		'add_new_item'          => __( 'Add New Payne Dealer', 'sqms-prod-selector' ),
		'add_new'               => __( 'Add New', 'sqms-prod-selector' ),
		'new_item'              => __( 'New Payne Dealer', 'sqms-prod-selector' ),
		'edit_item'             => __( 'Edit Payne Dealer', 'sqms-prod-selector' ),
		'update_item'           => __( 'Update Payne Dealer', 'sqms-prod-selector' ),
		'view_item'             => __( 'View Payne Dealer', 'sqms-prod-selector' ),
		'search_items'          => __( 'Search Payne Dealers', 'sqms-prod-selector' ),
		'not_found'             => __( 'Not found', 'sqms-prod-selector' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'sqms-prod-selector' ),
		'featured_image'        => __( 'Featured Image', 'sqms-prod-selector' ),
		'set_featured_image'    => __( 'Set featured image', 'sqms-prod-selector' ),
		'remove_featured_image' => __( 'Remove featured image', 'sqms-prod-selector' ),
		'use_featured_image'    => __( 'Use as featured image', 'sqms-prod-selector' ),
		'insert_into_item'      => __( 'Insert into item', 'sqms-prod-selector' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'sqms-prod-selector' ),
		'items_list'            => __( 'Items list', 'sqms-prod-selector' ),
		'items_list_navigation' => __( 'Items list navigation', 'sqms-prod-selector' ),
		'filter_items_list'     => __( 'Filter items list', 'sqms-prod-selector' ),
	);
	$rewrite = array(
		'slug'                  => 'payne-dealer',
		'with_front'            => false,
		'pages'                 => false,
		'feeds'                 => false,
	);
	$args = array(
		'label'                 => __( 'Payne Dealer', 'sqms-prod-selector' ),
		'description'           => __( 'Post Type Description', 'sqms-prod-selector' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'taxonomies'		=> array( 'zip_code', 'zone' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-id',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'rewrite'               => $rewrite,
		'capability_type'       => 'page',
	);
	register_post_type( 'sqms_payne_dealer', $args );

}

// Register Custom Taxonomy for dealer zip codes
function sqms_register_dealer_zip() {

	$labels = array(
		'name'                       => _x( 'Zip Codes', 'Taxonomy General Name', 'sqms-prod-selector' ),
		'singular_name'              => _x( 'Zip Code', 'Taxonomy Singular Name', 'sqms-prod-selector' ),
		'menu_name'                  => __( 'Zip Codes', 'sqms-prod-selector' ),
		'all_items'                  => __( 'All Zip Codes', 'sqms-prod-selector' ),
		'parent_item'                => __( 'Parent Zip Code', 'sqms-prod-selector' ),
		'parent_item_colon'          => __( 'Parent Zip Code:', 'sqms-prod-selector' ),
		'new_item_name'              => __( 'New Item Zip Code', 'sqms-prod-selector' ),
		'add_new_item'               => __( 'Add New Zip Code', 'sqms-prod-selector' ),
		'edit_item'                  => __( 'Edit Zip Code', 'sqms-prod-selector' ),
		'update_item'                => __( 'Update Zip Code', 'sqms-prod-selector' ),
		'view_item'                  => __( 'View Zip Code', 'sqms-prod-selector' ),
		'separate_items_with_commas' => __( 'Separate zip codes with commas', 'sqms-prod-selector' ),
		'add_or_remove_items'        => __( 'Add or remove zip codes', 'sqms-prod-selector' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'sqms-prod-selector' ),
		'popular_items'              => __( 'Popular zip codes', 'sqms-prod-selector' ),
		'search_items'               => __( 'Search Zip Codes', 'sqms-prod-selector' ),
		'not_found'                  => __( 'Not Found', 'sqms-prod-selector' ),
		'no_terms'                   => __( 'No zip codes', 'sqms-prod-selector' ),
		'items_list'                 => __( 'Zip Codes list', 'sqms-prod-selector' ),
		'items_list_navigation'      => __( 'Zip Codes list navigation', 'sqms-prod-selector' ),
	);
	$rewrite = array(
		'slug'                       => 'zip-code',
		'with_front'                 => false,
		'hierarchical'               => false,
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'rewrite'                    => $rewrite,
	);
	register_taxonomy( 'zip_code', array( 'sqms_payne_dealer' ), $args );

}


// Register Custom Taxonomy for dealer zones
function sqms_register_dealer_zone() {

	$labels = array(
		'name'                       => _x( 'Zones', 'Taxonomy General Name', 'sqms-prod-selector' ),
		'singular_name'              => _x( 'Zone', 'Taxonomy Singular Name', 'sqms-prod-selector' ),
		'menu_name'                  => __( 'Zones', 'sqms-prod-selector' ),
		'all_items'                  => __( 'All Zones', 'sqms-prod-selector' ),
		'parent_item'                => __( 'Parent Zone', 'sqms-prod-selector' ),
		'parent_item_colon'          => __( 'Parent Zone:', 'sqms-prod-selector' ),
		'new_item_name'              => __( 'New Item Zone', 'sqms-prod-selector' ),
		'add_new_item'               => __( 'Add New Zone', 'sqms-prod-selector' ),
		'edit_item'                  => __( 'Edit Zone', 'sqms-prod-selector' ),
		'update_item'                => __( 'Update Zone', 'sqms-prod-selector' ),
		'view_item'                  => __( 'View Zone', 'sqms-prod-selector' ),
		'separate_items_with_commas' => __( 'Separate zones with commas', 'sqms-prod-selector' ),
		'add_or_remove_items'        => __( 'Add or remove zones', 'sqms-prod-selector' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'sqms-prod-selector' ),
		'popular_items'              => __( 'Popular zones', 'sqms-prod-selector' ),
		'search_items'               => __( 'Search Zones', 'sqms-prod-selector' ),
		'not_found'                  => __( 'Not Found', 'sqms-prod-selector' ),
		'no_terms'                   => __( 'No zones', 'sqms-prod-selector' ),
		'items_list'                 => __( 'Zones list', 'sqms-prod-selector' ),
		'items_list_navigation'      => __( 'Zones list navigation', 'sqms-prod-selector' ),
	);
	$rewrite = array(
		'slug'                       => 'zone',
		'with_front'                 => false,
		'hierarchical'               => false,
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'rewrite'                    => $rewrite,
	);
	register_taxonomy( 'zone', array( 'sqms_payne_dealer' ), $args );

}


function add_builder_to_posttype($metabox)
{
	foreach($metabox as &$meta)
	{
		if($meta['id'] == 'avia_builder' || $meta['id'] == 'layout')
		{
			$meta['page'][] = 'sqms_payne_dealer';
		}
	}

	return $metabox;
}

  function no_post_nav($entries)
  {
      if(get_post_type() == 'sqms_payne_dealer') $entries = array();
      return $entries;
  }


  function ssla_add_id_column( $columns ) {
  	$checkbox = array_slice( $columns , 0, 1 );
  	$columns = array_slice( $columns , 1 );

  	$id['revealid_id'] = 'ID';

  	$columns = array_merge( $checkbox, $id, $columns );
  	return $columns;
  }

  function ssla_add_id_column_content( $column, $id ) {
    if( 'revealid_id' == $column ) {
      echo $id;
    }
  }
