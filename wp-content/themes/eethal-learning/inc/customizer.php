<?php
/**
 * Customizer settings.
 *
 * Every landing-page section has its own Customizer section with a
 * "Show this section" toggle plus a control for each piece of text, list,
 * link and image it renders.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a checkbox.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function eethal_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitize a non-negative integer.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function eethal_sanitize_number( $value ) {
	return absint( $value );
}

/**
 * Register Customizer panels, sections and settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function eethal_customize_register( $wp_customize ) {

	$defaults = eethal_defaults();

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_panel(
		'eethal_panel',
		array(
			'title'       => esc_html__( 'Eethal Front Page', 'eethal-learning' ),
			'description' => esc_html__( 'Content and settings for the landing page sections.', 'eethal-learning' ),
			'priority'    => 20,
		)
	);

	/**
	 * Add a Customizer section to the panel.
	 *
	 * @param string $id    Section id.
	 * @param string $title Section title.
	 * @param string $desc  Optional description (HTML allowed).
	 */
	$section = function ( $id, $title, $desc = '' ) use ( $wp_customize ) {
		static $priority = 10;
		$wp_customize->add_section(
			$id,
			array(
				'title'       => $title,
				'panel'       => 'eethal_panel',
				'description' => $desc,
				'priority'    => $priority++,
			)
		);
	};

	/**
	 * Add a text-like setting + control in one call.
	 *
	 * @param string $id      Setting key without prefix.
	 * @param string $section Section id.
	 * @param string $label   Control label.
	 * @param string $type    Control type.
	 * @param string $desc    Description.
	 */
	$add = function ( $id, $section, $label, $type = 'text', $desc = '' ) use ( $wp_customize, $defaults ) {
		$sanitize = 'sanitize_text_field';
		if ( 'url' === $type ) {
			$sanitize = 'esc_url_raw';
		} elseif ( 'email' === $type ) {
			$sanitize = 'sanitize_email';
		} elseif ( 'textarea' === $type ) {
			$sanitize = 'sanitize_textarea_field';
		} elseif ( 'number' === $type ) {
			$sanitize = 'eethal_sanitize_number';
		} elseif ( 'checkbox' === $type ) {
			$sanitize = 'eethal_sanitize_checkbox';
		}

		$wp_customize->add_setting(
			'eethal_' . $id,
			array(
				'default'           => isset( $defaults[ $id ] ) ? $defaults[ $id ] : '',
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'eethal_' . $id,
			array(
				'label'       => $label,
				'section'     => $section,
				'type'        => $type,
				'description' => $desc,
			)
		);
	};

	/**
	 * Add an image (attachment id) setting + media control.
	 *
	 * @param string $id      Setting key without prefix.
	 * @param string $section Section id.
	 * @param string $label   Control label.
	 */
	$add_image = function ( $id, $section, $label ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			'eethal_' . $id,
			array(
				'default'           => '',
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'eethal_' . $id,
				array(
					'label'     => $label,
					'section'   => $section,
					'mime_type' => 'image',
				)
			)
		);
	};

	$show       = esc_html__( 'Show this section', 'eethal-learning' );
	$label_txt  = esc_html__( 'Eyebrow label', 'eethal-learning' );
	$title_txt  = esc_html__( 'Heading', 'eethal-learning' );
	$icon_help  = esc_html__( 'One item per line, as "icon | text" — e.g. "🎓 | 10+ Years Exp Trainers". The icon can be any emoji.', 'eethal-learning' );
	$lines_help = esc_html__( 'One item per line.', 'eethal-learning' );
	$url_help   = esc_html__( 'Leave empty to use the enrolment link from Contact & Links.', 'eethal-learning' );

	/**
	 * Description with a link to the admin screen for a content type.
	 *
	 * @param string $post_type Post type.
	 * @param string $text      Link text.
	 * @return string
	 */
	$manage = function ( $post_type, $text ) {
		return sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( admin_url( 'edit.php?post_type=' . $post_type ) ),
			esc_html( $text )
		);
	};

	// -----------------------------------------------------------------
	// Contact & links.
	// -----------------------------------------------------------------
	$section( 'eethal_contact', esc_html__( 'Contact & Links', 'eethal-learning' ) );

	$add( 'phone', 'eethal_contact', esc_html__( 'Phone number (displayed)', 'eethal-learning' ) );
	$add( 'whatsapp', 'eethal_contact', esc_html__( 'WhatsApp number', 'eethal-learning' ), 'text', esc_html__( 'Digits only, including country code — e.g. 919585907643. Leave empty to hide the header WhatsApp button.', 'eethal-learning' ) );
	$add( 'whatsapp_message', 'eethal_contact', esc_html__( 'Pre-filled WhatsApp message', 'eethal-learning' ) );
	$add( 'email', 'eethal_contact', esc_html__( 'Email address', 'eethal-learning' ), 'email' );
	$add( 'enroll_url', 'eethal_contact', esc_html__( 'Enrolment form link', 'eethal-learning' ), 'url', esc_html__( 'Used by every "Enroll Now" and course button that has no link of its own. Leave empty to use the built-in Enroll Now form (/enroll/).', 'eethal-learning' ) );
	$add( 'particles', 'eethal_contact', esc_html__( 'Animated particle background', 'eethal-learning' ), 'checkbox' );

	// -----------------------------------------------------------------
	// Hero.
	// -----------------------------------------------------------------
	$section( 'eethal_hero', esc_html__( 'Hero Section', 'eethal-learning' ) );

	$add( 'show_hero', 'eethal_hero', $show, 'checkbox' );
	$add( 'hero_eyebrow', 'eethal_hero', esc_html__( 'Eyebrow text', 'eethal-learning' ) );
	$add( 'hero_title', 'eethal_hero', esc_html__( 'Headline', 'eethal-learning' ), 'textarea', esc_html__( 'Line breaks are preserved.', 'eethal-learning' ) );
	$add( 'hero_desc', 'eethal_hero', esc_html__( 'Description', 'eethal-learning' ), 'textarea' );
	$add( 'hero_btn', 'eethal_hero', esc_html__( 'Button text', 'eethal-learning' ), 'text', esc_html__( 'Leave empty to hide the button.', 'eethal-learning' ) );
	$add( 'hero_btn_url', 'eethal_hero', esc_html__( 'Button link', 'eethal-learning' ), 'url', $url_help );
	$add_image( 'hero_image', 'eethal_hero', esc_html__( 'Hero image', 'eethal-learning' ) );

	// -----------------------------------------------------------------
	// Benefits bar.
	// -----------------------------------------------------------------
	$section( 'eethal_benefits', esc_html__( 'Benefits Bar', 'eethal-learning' ) );

	$add( 'show_benefits', 'eethal_benefits', $show, 'checkbox' );
	$add( 'benefits_items', 'eethal_benefits', esc_html__( 'Benefit items', 'eethal-learning' ), 'textarea', $icon_help );

	// -----------------------------------------------------------------
	// Marquee.
	// -----------------------------------------------------------------
	$section( 'eethal_marquee', esc_html__( 'Scrolling Marquee', 'eethal-learning' ) );

	$add( 'show_marquee', 'eethal_marquee', $show, 'checkbox' );
	$add( 'marquee_items', 'eethal_marquee', esc_html__( 'Marquee items', 'eethal-learning' ), 'textarea', $lines_help );

	// -----------------------------------------------------------------
	// About.
	// -----------------------------------------------------------------
	$section( 'eethal_about', esc_html__( 'About Section', 'eethal-learning' ) );

	$add( 'show_about', 'eethal_about', $show, 'checkbox' );
	$add( 'about_label', 'eethal_about', $label_txt );
	$add( 'about_title', 'eethal_about', $title_txt, 'textarea', esc_html__( 'Line breaks are preserved.', 'eethal-learning' ) );
	$add( 'about_text_1', 'eethal_about', esc_html__( 'First paragraph', 'eethal-learning' ), 'textarea' );
	$add( 'about_text_2', 'eethal_about', esc_html__( 'Second paragraph', 'eethal-learning' ), 'textarea' );
	$add( 'about_checklist', 'eethal_about', esc_html__( 'Check list', 'eethal-learning' ), 'textarea', $lines_help );
	$add( 'stat_students', 'eethal_about', esc_html__( 'Stat 1 — number', 'eethal-learning' ), 'number', esc_html__( 'Also used in the Student Outcomes section.', 'eethal-learning' ) );
	$add( 'stat_students_suffix', 'eethal_about', esc_html__( 'Stat 1 — suffix', 'eethal-learning' ) );
	$add( 'stat_students_label', 'eethal_about', esc_html__( 'Stat 1 — label', 'eethal-learning' ) );
	$add( 'stat_placement', 'eethal_about', esc_html__( 'Stat 2 — number', 'eethal-learning' ), 'number', esc_html__( 'Also used in the Student Outcomes section.', 'eethal-learning' ) );
	$add( 'stat_placement_suffix', 'eethal_about', esc_html__( 'Stat 2 — suffix', 'eethal-learning' ) );
	$add( 'stat_placement_label', 'eethal_about', esc_html__( 'Stat 2 — label', 'eethal-learning' ) );
	$add_image( 'about_image', 'eethal_about', esc_html__( 'About image', 'eethal-learning' ) );

	// -----------------------------------------------------------------
	// Courses.
	// -----------------------------------------------------------------
	$section(
		'eethal_courses',
		esc_html__( 'Courses Section', 'eethal-learning' ),
		sprintf( esc_html__( 'Course cards (name, image, subtitle, button) are edited under %s.', 'eethal-learning' ), $manage( 'eethal_course', __( 'Courses', 'eethal-learning' ) ) )
	);

	$add( 'show_courses', 'eethal_courses', $show, 'checkbox' );
	$add( 'courses_label', 'eethal_courses', $label_txt );
	$add( 'courses_title', 'eethal_courses', $title_txt );
	$add( 'course_btn_text', 'eethal_courses', esc_html__( 'Default card button text', 'eethal-learning' ), 'text', esc_html__( 'Used when a course has no button text of its own.', 'eethal-learning' ) );

	// -----------------------------------------------------------------
	// Why choose us.
	// -----------------------------------------------------------------
	$section( 'eethal_why', esc_html__( 'Why Choose Us', 'eethal-learning' ) );

	$add( 'show_why_choose_us', 'eethal_why', $show, 'checkbox' );
	$add( 'why_label', 'eethal_why', $label_txt );
	$add( 'why_title', 'eethal_why', $title_txt );
	$add( 'why_items', 'eethal_why', esc_html__( 'Advantage items', 'eethal-learning' ), 'textarea', $icon_help );

	// -----------------------------------------------------------------
	// Bonding.
	// -----------------------------------------------------------------
	$section( 'eethal_bonding', esc_html__( 'Community Photo', 'eethal-learning' ) );

	$add( 'bonding_enable', 'eethal_bonding', $show, 'checkbox' );
	$add( 'bonding_label', 'eethal_bonding', $label_txt );
	$add( 'bonding_title', 'eethal_bonding', $title_txt );
	$add_image( 'bonding_image', 'eethal_bonding', esc_html__( 'Community photo', 'eethal-learning' ) );

	// -----------------------------------------------------------------
	// Outcomes.
	// -----------------------------------------------------------------
	$section( 'eethal_outcomes', esc_html__( 'Student Outcomes', 'eethal-learning' ), esc_html__( 'The two numbers are set in the About Section.', 'eethal-learning' ) );

	$add( 'show_outcomes', 'eethal_outcomes', $show, 'checkbox' );
	$add( 'outcomes_label', 'eethal_outcomes', $label_txt );
	$add( 'outcomes_title', 'eethal_outcomes', $title_txt );
	$add( 'outcomes_text', 'eethal_outcomes', esc_html__( 'Paragraph', 'eethal-learning' ), 'textarea' );
	$add( 'outcomes_checklist', 'eethal_outcomes', esc_html__( 'Check list', 'eethal-learning' ), 'textarea', $lines_help );
	$add( 'outcomes_stat_1_label', 'eethal_outcomes', esc_html__( 'Stat card 1 — label', 'eethal-learning' ) );
	$add( 'outcomes_stat_2_label', 'eethal_outcomes', esc_html__( 'Stat card 2 — label', 'eethal-learning' ) );
	$add_image( 'outcomes_image', 'eethal_outcomes', esc_html__( 'Outcomes image', 'eethal-learning' ) );

	// -----------------------------------------------------------------
	// Mentors.
	// -----------------------------------------------------------------
	$section(
		'eethal_mentors',
		esc_html__( 'Mentors Section', 'eethal-learning' ),
		sprintf( esc_html__( 'Mentor cards are edited under %s.', 'eethal-learning' ), $manage( 'eethal_mentor', __( 'Mentors', 'eethal-learning' ) ) )
	);

	$add( 'show_mentors', 'eethal_mentors', $show, 'checkbox' );
	$add( 'mentors_label', 'eethal_mentors', $label_txt );
	$add( 'mentors_title', 'eethal_mentors', $title_txt );
	$add( 'mentors_sub', 'eethal_mentors', esc_html__( 'Sub-heading', 'eethal-learning' ), 'textarea' );

	// -----------------------------------------------------------------
	// Testimonials.
	// -----------------------------------------------------------------
	$section(
		'eethal_testimonials',
		esc_html__( 'Alumni Stories', 'eethal-learning' ),
		sprintf( esc_html__( 'Alumni quotes are edited under %s.', 'eethal-learning' ), $manage( 'eethal_testimonial', __( 'Testimonials', 'eethal-learning' ) ) )
	);

	$add( 'show_testimonials', 'eethal_testimonials', $show, 'checkbox' );
	$add( 'test_label', 'eethal_testimonials', $label_txt );
	$add( 'test_title', 'eethal_testimonials', $title_txt );

	// -----------------------------------------------------------------
	// FAQ.
	// -----------------------------------------------------------------
	$section(
		'eethal_faq',
		esc_html__( 'FAQ Section', 'eethal-learning' ),
		sprintf( esc_html__( 'Questions and answers are edited under %s.', 'eethal-learning' ), $manage( 'eethal_faq', __( 'FAQs', 'eethal-learning' ) ) )
	);

	$add( 'show_faq', 'eethal_faq', $show, 'checkbox' );
	$add( 'faq_label', 'eethal_faq', $label_txt );
	$add( 'faq_title', 'eethal_faq', $title_txt );

	// -----------------------------------------------------------------
	// CTA & footer.
	// -----------------------------------------------------------------
	$section( 'eethal_cta', esc_html__( 'Call to Action & Footer', 'eethal-learning' ) );

	$add( 'show_cta', 'eethal_cta', esc_html__( 'Show the call-to-action band', 'eethal-learning' ), 'checkbox' );
	$add( 'cta_title', 'eethal_cta', esc_html__( 'CTA heading', 'eethal-learning' ) );
	$add( 'cta_text', 'eethal_cta', esc_html__( 'CTA text', 'eethal-learning' ), 'textarea' );
	$add( 'cta_btn', 'eethal_cta', esc_html__( 'Primary button text', 'eethal-learning' ), 'text', esc_html__( 'Leave empty to hide the button.', 'eethal-learning' ) );
	$add( 'cta_btn_url', 'eethal_cta', esc_html__( 'Primary button link', 'eethal-learning' ), 'url', $url_help );
	$add( 'cta_btn_2', 'eethal_cta', esc_html__( 'Secondary button text', 'eethal-learning' ), 'text', esc_html__( 'Leave empty to hide the button.', 'eethal-learning' ) );
	$add( 'cta_btn_2_url', 'eethal_cta', esc_html__( 'Secondary button link', 'eethal-learning' ), 'text', esc_html__( 'Leave empty to open an email to the address in Contact & Links.', 'eethal-learning' ) );
	$add( 'cta_brand', 'eethal_cta', esc_html__( 'Brand name in CTA block', 'eethal-learning' ) );
	$add( 'cta_phone_label', 'eethal_cta', esc_html__( 'Phone label', 'eethal-learning' ) );
	$add( 'cta_email_label', 'eethal_cta', esc_html__( 'Email label', 'eethal-learning' ) );
	$add( 'footer_text', 'eethal_cta', esc_html__( 'Footer credit line', 'eethal-learning' ), 'text', esc_html__( 'The copyright symbol and current year are added automatically.', 'eethal-learning' ) );

	// -----------------------------------------------------------------
	// Enroll Now form and Form Spam Protection. The fields are listed once in
	// eethal_enroll_setting_groups() (inc/enroll-admin.php), which also builds the
	// Enroll Now page in wp-admin, so both screens edit the same settings.
	// -----------------------------------------------------------------
	foreach ( eethal_enroll_setting_groups() as $group_id => $group ) {
		$section( $group_id, esc_html( $group['title'] ), esc_html( $group['desc'] ) );
		foreach ( $group['fields'] as $id => $field ) {
			$add( $id, $group_id, esc_html( $field['label'] ), $field['type'], esc_html( $field['desc'] ) );
		}
	}
}
add_action( 'customize_register', 'eethal_customize_register' );

/**
 * Sanitize the secondary CTA link, which may be a URL, mailto: or tel: link.
 *
 * @param string $value Raw value.
 * @return string
 */
function eethal_sanitize_link( $value ) {
	return esc_url_raw( trim( (string) $value ), array( 'http', 'https', 'mailto', 'tel' ) );
}

/**
 * Swap the generic sanitizer on the secondary CTA link for one that allows mailto:/tel:.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function eethal_customize_link_sanitizer( $wp_customize ) {
	$setting = $wp_customize->get_setting( 'eethal_cta_btn_2_url' );
	if ( $setting ) {
		$setting->sanitize_callback = 'eethal_sanitize_link';
	}
}
add_action( 'customize_register', 'eethal_customize_link_sanitizer', 20 );

/**
 * Live preview for the site title.
 */
function eethal_customize_preview_js() {
	wp_enqueue_script(
		'eethal-customizer-preview',
		EETHAL_URI . '/assets/js/customizer.js',
		array( 'customize-preview' ),
		EETHAL_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'eethal_customize_preview_js' );
