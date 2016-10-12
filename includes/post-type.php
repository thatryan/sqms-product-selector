<?php

add_action( 'init', 'sqms_register_productselector_post_type', 0 );
add_action( 'init', 'sqms_register_payne_dealer', 0 );
add_filter('avf_builder_boxes', 'add_builder_to_posttype');

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
		'supports'              => array( 'title', 'editor', 'thumbnail', ),
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
