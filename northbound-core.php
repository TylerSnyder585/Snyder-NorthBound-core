<?php
/**
 * Plugin Name: NorthBound Core
 * Description: Custom content for NorthBound Coffee Co. site.
 * Version: 1.0
 * Author: Tyler Snyder
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stop if this file is opened directly.
}
 
add_action( 'init', 'northbound_register_content' );
 
function northbound_register_content() {
 
	// 1. Post type
	register_post_type( 'menu_item', array(
		'labels'       => array(
			'name'          => 'Menu Items',
			'singular_name' => 'Menu Item',
			'add_new_item'  => 'Add New Menu Item',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'rewrite'      => array( 'slug' => 'menu' ),
		'menu_icon'    => 'dashicons-coffee',
	) );
 
	// 2. Taxonomy
	register_taxonomy( 'menu_category', 'menu_item', array(
		'labels'       => array(
			'name'          => 'Menu Categories',
			'singular_name' => 'Menu Category',
		),
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'menu-category' ),
	) );
 
	// 3. Custom fields
	foreach ( array( 'price', 'dietary', 'size' ) as $key ) {
		register_post_meta( 'menu_item', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
		) );
	}
}


// ===== Week 5 additions =====
 
// 1. Shortcode: [latest_menu count="3"]
add_shortcode( 'latest_menu', 'northbound_latest_menu' );
 
function northbound_latest_menu( $atts ) {
	$atts  = shortcode_atts( 
		array( 
			'count' => 3,
			'show_price' => 'yes', 
			), 
		$atts, 'latest_menu' 
	);

	$items = get_posts( array(
		'post_type'   => 'menu_item',
		'numberposts' => absint( $atts['count'] ),
	) );
 
	if ( empty( $items ) ) {
		return '<p class="rr-latest-empty">Check back soon for new items.</p>';
	}
 
	$html = '<ul class="rr-latest">';
	foreach ( $items as $item ) {
		$price = get_post_meta( $item->ID, 'price', true );
		$html .= '<li><a href="' . esc_url( get_permalink( $item ) ) . '">'
			. esc_html( get_the_title( $item ) ) . '</a>';
		if ( $price && 'yes' === $atts['show_price'] ) {
			$html .= ' <span class="rr-price">' . esc_html( $price ) . '</span>';
		}
		$html .= '</li>';
	}
	return $html . '</ul>';
}
 
// 2. Filter: add a call to action after every blog post
add_filter( 'the_content', 'northbound_add_cta', 20 );
 
function northbound_add_cta( $content ) {
	if ( ! is_singular( 'post' ) || ! is_main_query() ) {
		return $content; // Leave everything else alone.
	}
	$cta = '<div class="rr-cta"><p>Explore our latest coffee and bakery offerings.</p>'
		. '<a href="' . esc_url( home_url( '/menu/' ) ) . '">View Our Menu</a></div>';
	return $content . $cta;
}
 
// 3. Enqueue the plugin's stylesheet
add_action( 'wp_enqueue_scripts', 'northbound_enqueue_styles' );
 
function northbound_enqueue_styles() {
	wp_enqueue_style(
		'northbound-core',
		plugins_url( 'css/core.css', __FILE__ ),
		array(),
		'1.0'
	);
}
 
// 4. Block pattern
add_action( 'init', 'northbound_register_patterns' );
 
function northbound_register_patterns() {
	register_block_pattern( 'northbound/visit-us', array(
		'title'      => 'Visit Us',
		'categories' => array( 'call-to-action' ),
		'content'    => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Visit us today</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Open daily, 7 a.m. to 3 p.m.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
	) );
}
