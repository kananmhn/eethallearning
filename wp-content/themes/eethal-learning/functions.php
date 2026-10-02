<?php
/**
 * Eethal Learning theme functions and definitions.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'EETHAL_VERSION' ) ) {
	define( 'EETHAL_VERSION', '1.0.0' );
}

define( 'EETHAL_DIR', get_template_directory() );
define( 'EETHAL_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function eethal_setup() {
	load_theme_textdomain( 'eethal-learning', EETHAL_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'eethal-learning' ),
		)
	);

	// Course card thumbnails.
	add_image_size( 'eethal-course', 600, 400, true );
}
add_action( 'after_setup_theme', 'eethal_setup' );

/**
 * Content width.
 */
function eethal_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'eethal_content_width', 1200 );
}
add_action( 'after_setup_theme', 'eethal_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function eethal_scripts() {
	// Google Fonts (Inter + Outfit), matching the original design.
	wp_enqueue_style(
		'eethal-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
		array(),
		'6.4.0'
	);

	wp_enqueue_style( 'eethal-style', get_stylesheet_uri(), array( 'eethal-fonts' ), EETHAL_VERSION );

	wp_enqueue_script( 'eethal-main', EETHAL_URI . '/assets/js/main.js', array(), EETHAL_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'eethal_scripts' );

/**
 * Preconnect to the Google Fonts CDN.
 */
function eethal_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && wp_style_is( 'eethal-fonts', 'queue' ) ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => '',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'eethal_resource_hints', 10, 2 );

/**
 * Footer widget area (used on the blog/inner pages).
 */
function eethal_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Blog Sidebar', 'eethal-learning' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Appears alongside posts and archive pages.', 'eethal-learning' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'eethal_widgets_init' );

/**
 * Body classes.
 */
function eethal_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	if ( is_front_page() ) {
		$classes[] = 'eethal-landing';
	} else {
		$classes[] = 'eethal-inner';
	}
	return $classes;
}
add_filter( 'body_class', 'eethal_body_classes' );

/**
 * Fall back to the bundled favicon when no Site Icon is set.
 */
function eethal_fallback_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf(
		'<link rel="icon" href="%s" type="image/png">' . "\n",
		esc_url( EETHAL_URI . '/assets/images/favico.png' )
	);
}
add_action( 'wp_head', 'eethal_fallback_favicon' );

require EETHAL_DIR . '/inc/defaults.php';
require EETHAL_DIR . '/inc/template-functions.php';
require EETHAL_DIR . '/inc/post-types.php';
require EETHAL_DIR . '/inc/meta-boxes.php';
require EETHAL_DIR . '/inc/customizer.php';
require EETHAL_DIR . '/inc/seed.php';
require EETHAL_DIR . '/inc/talent-directory.php';
require EETHAL_DIR . '/inc/talent-entries.php';
require EETHAL_DIR . '/inc/talent-activity.php';
require EETHAL_DIR . '/inc/enrollments.php';
