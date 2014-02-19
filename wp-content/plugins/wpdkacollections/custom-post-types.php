<?php
/**
 * @package WP DKA Collections
 * @version 1.0
 */

function wpdkacollections_register_exhibition_type() {

	$post_type = 'udstilling';
	$rewrite_slug = 'samling/'.$post_type;

	register_post_type( $post_type, array(
		'labels'             => array(
			'name'               => __('Exhibitions',WPDKACollections::DOMAIN),
			'singular_name'      => __('Exhibition',WPDKACollections::DOMAIN),
			'add_new'            => _x('Add New','exhibition',WPDKACollections::DOMAIN),
			'add_new_item'       => __('Add New Exhibition',WPDKACollections::DOMAIN),
			'edit_item'          => __('Edit Exhibition',WPDKACollections::DOMAIN),
			'new_item'           => __('New Exhibition',WPDKACollections::DOMAIN),
			'all_items'          => __('All Exhibitions',WPDKACollections::DOMAIN),
			'view_item'          => __('View Exhibition',WPDKACollections::DOMAIN),
			'search_items'       => __('Search Exhibitions',WPDKACollections::DOMAIN),
			'not_found'          => __('No Exhibitions found',WPDKACollections::DOMAIN),
			'not_found_in_trash' => __('No Exhibitions found in Trash',WPDKACollections::DOMAIN),
		),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => $rewrite_slug ),
		'capabilities'       => 	array(
			'edit_post'		 => WPDKACollections::CAPABILITY,
			'read_post'		 => WPDKACollections::CAPABILITY,
			'delete_post'		 => WPDKACollections::CAPABILITY,
			'edit_posts'		 => WPDKACollections::CAPABILITY,
			'edit_others_posts'	 => WPDKACollections::CAPABILITY,
			'publish_posts'		 => WPDKACollections::CAPABILITY,
			'read_private_posts'	 => WPDKACollections::CAPABILITY,
			'delete_posts'         => WPDKACollections::CAPABILITY,
			'delete_private_posts'   => WPDKACollections::CAPABILITY,
			'delete_published_posts' => WPDKACollections::CAPABILITY,
			'delete_others_posts'    => WPDKACollections::CAPABILITY,
			'edit_private_posts'     => WPDKACollections::CAPABILITY,
			'edit_published_posts'   => WPDKACollections::CAPABILITY,
		),
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'supports'           => array( 'title', 'editor', 'author', 'revisions', 'excerpt' )
	));
}
add_action( 'init', 'wpdkacollections_register_exhibition_type' );


function wpdkacollections_rewrite_flush() {
	wpdkacollections_register_exhibition_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpdkacollections_rewrite_flush' );

//eol