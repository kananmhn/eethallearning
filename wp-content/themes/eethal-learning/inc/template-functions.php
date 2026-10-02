<?php
/**
 * Template helpers.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a theme option, falling back to the packaged default.
 *
 * @param string $key     Setting key, without the `eethal_` prefix.
 * @param mixed  $default Optional explicit fallback.
 * @return mixed
 */
function eethal_opt( $key, $default = null ) {
	$defaults = eethal_defaults();
	if ( null === $default && isset( $defaults[ $key ] ) ) {
		$default = $defaults[ $key ];
	}
	return get_theme_mod( 'eethal_' . $key, $default );
}

/**
 * Echo a theme option, escaped for HTML output.
 *
 * @param string $key Setting key.
 */
function eethal_the_opt( $key ) {
	echo esc_html( eethal_opt( $key ) );
}

/**
 * Echo a theme option, allowing a small set of inline tags.
 *
 * @param string $key Setting key.
 */
function eethal_the_opt_html( $key ) {
	echo wp_kses( eethal_opt( $key ), eethal_inline_tags() );
}

/**
 * Inline tags allowed in Customizer text fields.
 *
 * @return array
 */
function eethal_inline_tags() {
	return array(
		'br'     => array(),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'span'   => array( 'class' => array() ),
		'a'      => array(
			'href'   => array(),
			'target' => array(),
			'rel'    => array(),
			'class'  => array(),
		),
	);
}

/**
 * Convert newlines in a Customizer field to <br> tags.
 *
 * @param string $key Setting key.
 */
function eethal_the_opt_nl2br( $key ) {
	echo wp_kses( nl2br( esc_html( eethal_opt( $key ) ) ), array( 'br' => array() ) );
}

/**
 * Theme image URL.
 *
 * @param string $path Path relative to the theme root.
 * @return string
 */
function eethal_img( $path ) {
	return EETHAL_URI . '/' . ltrim( $path, '/' );
}

/**
 * The enrolment link: the built-in Enroll Now page, unless the Customizer sets another one.
 *
 * @return string
 */
function eethal_enroll_url() {
	return eethal_enroll_link( eethal_opt( 'enroll_url' ) );
}

/**
 * A button link, with empty links and the old Batch-8 Google Form (still saved in some
 * settings) sent to the built-in Enroll Now page.
 *
 * @param string $url Saved link.
 * @return string Escaped URL.
 */
function eethal_enroll_link( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url || false !== strpos( $url, '1FAIpQLScoWJQlJOVmkPIG3sMwN2i5CXEbpWjqk0RqwYW93exELfw2rA' ) ) {
		$url = eethal_td_page_urls()['enroll'];
	}
	return esc_url( $url );
}

/**
 * Attributes that open a link in a new tab, but only when it leaves this site.
 *
 * @param string $url Link.
 * @return string Attributes, ready to print.
 */
function eethal_link_target( $url ) {
	return 0 === strpos( $url, home_url() ) ? '' : 'target="_blank" rel="noopener noreferrer"';
}

/**
 * WhatsApp click-to-chat URL built from the configured number and message.
 *
 * @return string
 */
function eethal_whatsapp_url() {
	$number = preg_replace( '/\D/', '', (string) eethal_opt( 'whatsapp' ) );
	if ( '' === $number ) {
		return '';
	}
	$url = 'https://wa.me/' . $number;
	$msg = trim( (string) eethal_opt( 'whatsapp_message' ) );
	if ( '' !== $msg ) {
		$url = add_query_arg( 'text', rawurlencode( $msg ), $url );
	}
	return esc_url( $url );
}

/**
 * Fallback navigation used when no menu is assigned to the Primary location.
 */
function eethal_default_menu() {
	$items = array(
		'#about'          => __( 'About', 'eethal-learning' ),
		'#courses'        => __( 'Courses', 'eethal-learning' ),
		'#why-choose-us'  => __( 'Process', 'eethal-learning' ),
		'#testimonials'   => __( 'Alumni', 'eethal-learning' ),
		'#faq'            => __( 'FAQ', 'eethal-learning' ),
	);

	$home = is_front_page() ? '' : esc_url( home_url( '/' ) );

	echo '<ul id="primary-menu" class="menu">';
	foreach ( $items as $anchor => $label ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( $home . $anchor ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Query helper for the theme's content post types.
 *
 * @param string $post_type Post type key.
 * @param int    $limit     Number of items.
 * @return WP_Post[]
 */
function eethal_get_items( $post_type, $limit = -1 ) {
	$posts = get_posts(
		array(
			'post_type'        => $post_type,
			'posts_per_page'   => $limit,
			'orderby'          => 'menu_order date',
			'order'            => 'ASC',
			'suppress_filters' => false,
		)
	);
	return is_array( $posts ) ? $posts : array();
}

/**
 * Read a post meta value with a fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key, without the `_eethal_` prefix.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function eethal_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, '_eethal_' . $key, true );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Derive avatar initials from a name when none were entered.
 *
 * @param string $name Full name.
 * @return string
 */
function eethal_initials( $name ) {
	$name = trim( wp_strip_all_tags( $name ) );
	if ( '' === $name ) {
		return '';
	}
	$parts = preg_split( '/\s+/', $name );
	if ( count( $parts ) > 1 ) {
		$initials = mb_substr( $parts[0], 0, 1 ) . mb_substr( $parts[1], 0, 1 );
	} else {
		$initials = mb_substr( $parts[0], 0, 2 );
	}
	return mb_strtoupper( $initials );
}

/**
 * Pick a deterministic avatar colour class so cards vary without configuration.
 *
 * @param int   $index   Item index.
 * @param array $palette Class names to cycle through.
 * @return string
 */
function eethal_avatar_class( $index, $palette ) {
	return $palette[ $index % count( $palette ) ];
}

/**
 * A multi-line Customizer field as an array of non-empty lines.
 *
 * @param string $key Setting key.
 * @return array
 */
function eethal_lines( $key ) {
	$raw   = (string) eethal_opt( $key );
	$items = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	return array_values( $items );
}

/**
 * A multi-line "icon | text" Customizer field as an array of [ icon, text ] pairs.
 *
 * @param string $key Setting key.
 * @return array
 */
function eethal_icon_items( $key ) {
	$items = array();
	foreach ( eethal_lines( $key ) as $line ) {
		$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
		$items[] = 2 === count( $parts ) ? $parts : array( '', $parts[0] );
	}
	return $items;
}

/**
 * Marquee items as an array.
 *
 * @return array
 */
function eethal_marquee_items() {
	return eethal_lines( 'marquee_items' );
}

/**
 * Whether a landing-page section is switched on in the Customizer.
 *
 * @param string $slug Section slug, as used in template-parts/sections/.
 * @return bool
 */
function eethal_section_enabled( $slug ) {
	$key = 'bonding' === $slug ? 'bonding_enable' : 'show_' . str_replace( '-', '_', $slug );
	return (bool) eethal_opt( $key, true );
}

/**
 * A button link from the Customizer, falling back to the enrolment link.
 *
 * @param string $key Setting key.
 * @return string Escaped URL.
 */
function eethal_button_url( $key ) {
	$url = trim( (string) eethal_opt( $key ) );
	return eethal_enroll_link( '' === $url ? eethal_opt( 'enroll_url' ) : $url );
}

/**
 * Render a section eyebrow label + title pair.
 *
 * @param string $label_key Label option key.
 * @param string $title_key Title option key.
 * @param bool   $centered  Whether to centre the block.
 */
function eethal_section_heading( $label_key, $title_key, $centered = true ) {
	$style = $centered ? ' style="text-align:center"' : '';
	echo '<div class="section-heading"' . $style . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<div class="section-label reveal">' . esc_html( eethal_opt( $label_key ) ) . '</div>';
	echo '<h2 class="section-title reveal">' . wp_kses( nl2br( esc_html( eethal_opt( $title_key ) ) ), array( 'br' => array() ) ) . '</h2>';
	echo '</div>';
}

/**
 * Posts pagination for the blog templates.
 */
function eethal_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => esc_html__( 'Previous', 'eethal-learning' ),
			'next_text' => esc_html__( 'Next', 'eethal-learning' ),
		)
	);
}

/**
 * Entry meta line for blog posts.
 */
function eethal_entry_meta() {
	printf(
		'<div class="entry-meta"><span class="posted-on">%1$s</span><span class="byline">%2$s</span></div>',
		esc_html( get_the_date() ),
		esc_html( get_the_author() )
	);
}
