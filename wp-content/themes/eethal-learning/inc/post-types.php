<?php
/**
 * Custom post types powering the front page sections.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the content types.
 */
function eethal_register_post_types() {

	register_post_type(
		'eethal_course',
		array(
			'labels'        => array(
				'name'               => esc_html__( 'Courses', 'eethal-learning' ),
				'singular_name'      => esc_html__( 'Course', 'eethal-learning' ),
				'add_new_item'       => esc_html__( 'Add New Course', 'eethal-learning' ),
				'edit_item'          => esc_html__( 'Edit Course', 'eethal-learning' ),
				'new_item'           => esc_html__( 'New Course', 'eethal-learning' ),
				'view_item'          => esc_html__( 'View Course', 'eethal-learning' ),
				'search_items'       => esc_html__( 'Search Courses', 'eethal-learning' ),
				'not_found'          => esc_html__( 'No courses yet.', 'eethal-learning' ),
				'menu_name'          => esc_html__( 'Courses', 'eethal-learning' ),
				'featured_image'     => esc_html__( 'Course Image', 'eethal-learning' ),
				'set_featured_image' => esc_html__( 'Set course image', 'eethal-learning' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array( 'slug' => 'courses' ),
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'eethal_mentor',
		array(
			'labels'        => array(
				'name'          => esc_html__( 'Mentors', 'eethal-learning' ),
				'singular_name' => esc_html__( 'Mentor', 'eethal-learning' ),
				'add_new_item'  => esc_html__( 'Add New Mentor', 'eethal-learning' ),
				'edit_item'     => esc_html__( 'Edit Mentor', 'eethal-learning' ),
				'not_found'     => esc_html__( 'No mentors yet.', 'eethal-learning' ),
				'menu_name'     => esc_html__( 'Mentors', 'eethal-learning' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-businessperson',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'eethal_testimonial',
		array(
			'labels'        => array(
				'name'          => esc_html__( 'Testimonials', 'eethal-learning' ),
				'singular_name' => esc_html__( 'Testimonial', 'eethal-learning' ),
				'add_new_item'  => esc_html__( 'Add New Testimonial', 'eethal-learning' ),
				'edit_item'     => esc_html__( 'Edit Testimonial', 'eethal-learning' ),
				'not_found'     => esc_html__( 'No testimonials yet.', 'eethal-learning' ),
				'menu_name'     => esc_html__( 'Testimonials', 'eethal-learning' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'eethal_faq',
		array(
			'labels'        => array(
				'name'          => esc_html__( 'FAQs', 'eethal-learning' ),
				'singular_name' => esc_html__( 'FAQ', 'eethal-learning' ),
				'add_new_item'  => esc_html__( 'Add New FAQ', 'eethal-learning' ),
				'edit_item'     => esc_html__( 'Edit FAQ', 'eethal-learning' ),
				'not_found'     => esc_html__( 'No FAQs yet.', 'eethal-learning' ),
				'menu_name'     => esc_html__( 'FAQs', 'eethal-learning' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-editor-help',
			'menu_position' => 23,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'eethal_register_post_types' );

/**
 * Flush rewrite rules once after the theme is activated, so /courses/ resolves.
 */
function eethal_flush_rewrites() {
	eethal_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'eethal_flush_rewrites' );

/**
 * Helpful placeholder text in the editor title field.
 *
 * @param string  $title Default placeholder.
 * @param WP_Post $post  Current post.
 * @return string
 */
function eethal_title_placeholder( $title, $post ) {
	switch ( $post->post_type ) {
		case 'eethal_course':
			return esc_html__( 'Course name', 'eethal-learning' );
		case 'eethal_mentor':
			return esc_html__( 'Mentor name', 'eethal-learning' );
		case 'eethal_testimonial':
			return esc_html__( 'Student name', 'eethal-learning' );
		case 'eethal_faq':
			return esc_html__( 'Question', 'eethal-learning' );
	}
	return $title;
}
add_filter( 'enter_title_here', 'eethal_title_placeholder', 10, 2 );
