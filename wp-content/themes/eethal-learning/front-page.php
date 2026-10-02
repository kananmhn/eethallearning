<?php
/**
 * The landing page.
 *
 * Each section is a partial in template-parts/sections/ so sections can be
 * reordered, removed, or reused on other page templates.
 *
 * @package Eethal_Learning
 */

get_header();

$eethal_sections = apply_filters(
	'eethal_front_page_sections',
	array(
		'hero',
		'benefits',
		'marquee',
		'about',
		'courses',
		'why-choose-us',
		'bonding',
		'outcomes',
		'mentors',
		'testimonials',
		'faq',
	)
);

foreach ( $eethal_sections as $eethal_section ) {
	if ( eethal_section_enabled( $eethal_section ) ) {
		get_template_part( 'template-parts/sections/' . $eethal_section );
	}
}

get_footer();
