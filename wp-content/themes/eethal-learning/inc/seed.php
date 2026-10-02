<?php
/**
 * One-time content import.
 *
 * Turns the packaged defaults into real, editable WordPress content: Course,
 * Mentor, Testimonial and FAQ posts, Media Library images wired to the
 * Customizer image fields, the site logo, and a Primary menu. Runs once on
 * theme activation (or on the next admin page load for an already-active
 * theme) and never overwrites anything the site owner has created.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import an image bundled with the theme into the Media Library.
 *
 * @param string $rel Path relative to the theme root.
 * @return int Attachment ID, or 0 on failure.
 */
function eethal_import_theme_image( $rel ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_eethal_source', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$src = EETHAL_DIR . '/' . $rel;
	if ( ! file_exists( $src ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$tmp = wp_tempnam( basename( $src ) );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}

	$id = media_handle_sideload(
		array(
			'name'     => basename( $src ),
			'tmp_name' => $tmp,
		),
		0
	);

	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}

	update_post_meta( $id, '_eethal_source', $rel );
	return (int) $id;
}

/**
 * Create posts of a content type, unless that type already has any.
 *
 * @param string $post_type Post type.
 * @param array  $items     Items with title, content, meta and optional image.
 */
function eethal_seed_posts( $post_type, $items ) {
	$has_posts = get_posts(
		array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $has_posts ) {
		return;
	}

	foreach ( $items as $order => $item ) {
		$post_id = wp_insert_post(
			array(
				'post_type'    => $post_type,
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_content' => isset( $item['content'] ) ? $item['content'] : '',
				'menu_order'   => $order,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		foreach ( isset( $item['meta'] ) ? $item['meta'] : array() as $key => $value ) {
			update_post_meta( $post_id, '_eethal_' . $key, $value );
		}

		if ( ! empty( $item['image'] ) ) {
			$attachment_id = eethal_import_theme_image( $item['image'] );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}
	}
}

/**
 * Create the Primary menu with the landing-page anchors, if none is assigned.
 */
function eethal_seed_menu() {
	if ( has_nav_menu( 'primary' ) ) {
		return;
	}

	$menu    = wp_get_nav_menu_object( 'Primary Menu' );
	$menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( 'Primary Menu' );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	if ( ! $menu ) {
		$links = array(
			'#about'         => __( 'About', 'eethal-learning' ),
			'#courses'       => __( 'Courses', 'eethal-learning' ),
			'#why-choose-us' => __( 'Process', 'eethal-learning' ),
			'#testimonials'  => __( 'Alumni', 'eethal-learning' ),
			'#faq'           => __( 'FAQ', 'eethal-learning' ),
		);
		foreach ( $links as $anchor => $label ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => $label,
					'menu-item-url'    => home_url( '/' . $anchor ),
					'menu-item-type'   => 'custom',
					'menu-item-status' => 'publish',
				)
			);
		}
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Import all default content once.
 */
function eethal_seed_content() {
	if ( get_option( 'eethal_content_seeded' ) ) {
		return;
	}

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	// Customizer images, so each image field shows the picture currently in use.
	$images = array(
		'eethal_hero_image'     => 'assets/images/vtnf/hero-main.png',
		'eethal_about_image'    => 'assets/images/vtnf/training.png',
		'eethal_bonding_image'  => 'assets/images/vtnf/bonding-image.jpeg',
		'eethal_outcomes_image' => 'assets/images/students-outcome.jpeg',
		'custom_logo'           => 'assets/images/eethal-white-logo.png',
	);
	foreach ( $images as $mod => $rel ) {
		if ( get_theme_mod( $mod ) ) {
			continue;
		}
		$attachment_id = eethal_import_theme_image( $rel );
		if ( $attachment_id ) {
			set_theme_mod( $mod, $attachment_id );
		}
	}

	$courses = array();
	foreach ( eethal_default_courses() as $course ) {
		$courses[] = array(
			'title' => $course['title'],
			'image' => $course['image'],
			'meta'  => array(
				'subtitle' => $course['desc'],
				'btn_text' => __( 'Learn More', 'eethal-learning' ),
			),
		);
	}
	eethal_seed_posts( 'eethal_course', $courses );

	$mentors = array();
	foreach ( eethal_default_mentors() as $mentor ) {
		$mentors[] = array(
			'title'   => $mentor['name'],
			'content' => $mentor['bio'],
			'meta'    => array(
				'role'     => $mentor['role'],
				'initials' => $mentor['initials'],
				'colour'   => $mentor['colour'],
				'tags'     => implode( ', ', $mentor['tags'] ),
			),
		);
	}
	eethal_seed_posts( 'eethal_mentor', $mentors );

	$testimonials = array();
	foreach ( eethal_default_testimonials() as $testimonial ) {
		$testimonials[] = array(
			'title'   => $testimonial['name'],
			'content' => $testimonial['quote'],
			'meta'    => array(
				'role'     => $testimonial['role'],
				'initials' => $testimonial['initials'],
				'colour'   => $testimonial['colour'],
				'rating'   => '5',
			),
		);
	}
	eethal_seed_posts( 'eethal_testimonial', $testimonials );

	$faqs = array();
	foreach ( eethal_default_faqs() as $faq ) {
		$faqs[] = array(
			'title'   => $faq['q'],
			'content' => $faq['a'],
		);
	}
	eethal_seed_posts( 'eethal_faq', $faqs );

	eethal_seed_menu();

	update_option( 'eethal_content_seeded', EETHAL_VERSION );
}
add_action( 'after_switch_theme', 'eethal_seed_content', 20 );

/**
 * Run the import for a theme that was already active before this file existed.
 */
function eethal_maybe_seed_content() {
	if ( wp_doing_ajax() || get_option( 'eethal_content_seeded' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	eethal_seed_content();
}
add_action( 'admin_init', 'eethal_maybe_seed_content' );
