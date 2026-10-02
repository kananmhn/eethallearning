<?php
/**
 * Meta boxes for the theme's content types.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field definitions, keyed by post type.
 *
 * @return array
 */
function eethal_meta_fields() {
	return array(
		'eethal_course'      => array(
			'title' => __( 'Course Details', 'eethal-learning' ),
			'fields' => array(
				'subtitle' => array(
					'label' => __( 'Card subtitle', 'eethal-learning' ),
					'type'  => 'text',
					'desc'  => __( 'Short line under the course name, e.g. "Learn HTML, CSS &amp; Javascript". Falls back to the excerpt.', 'eethal-learning' ),
				),
				'link'     => array(
					'label' => __( 'Button link', 'eethal-learning' ),
					'type'  => 'url',
					'desc'  => __( 'Leave empty to use the global enrolment link from the Customizer.', 'eethal-learning' ),
				),
				'btn_text' => array(
					'label' => __( 'Button text', 'eethal-learning' ),
					'type'  => 'text',
					'desc'  => __( 'Defaults to "Learn More".', 'eethal-learning' ),
				),
			),
		),
		'eethal_mentor'      => array(
			'title'  => __( 'Mentor Details', 'eethal-learning' ),
			'fields' => array(
				'role'     => array(
					'label' => __( 'Role / designation', 'eethal-learning' ),
					'type'  => 'text',
				),
				'initials' => array(
					'label' => __( 'Avatar initials', 'eethal-learning' ),
					'type'  => 'text',
					'desc'  => __( 'Two letters. Generated from the name when left empty.', 'eethal-learning' ),
				),
				'colour'   => array(
					'label'   => __( 'Avatar colour', 'eethal-learning' ),
					'type'    => 'select',
					'options' => array(
						''    => __( 'Automatic', 'eethal-learning' ),
						'ia1' => __( 'Red', 'eethal-learning' ),
						'ia2' => __( 'Indigo', 'eethal-learning' ),
						'ia3' => __( 'Green', 'eethal-learning' ),
					),
				),
				'tags'     => array(
					'label' => __( 'Skill tags', 'eethal-learning' ),
					'type'  => 'text',
					'desc'  => __( 'Comma separated, e.g. "AEM, EDS, JavaScript".', 'eethal-learning' ),
				),
			),
		),
		'eethal_testimonial' => array(
			'title'  => __( 'Testimonial Details', 'eethal-learning' ),
			'fields' => array(
				'role'     => array(
					'label' => __( 'Job title', 'eethal-learning' ),
					'type'  => 'text',
				),
				'initials' => array(
					'label' => __( 'Avatar initials', 'eethal-learning' ),
					'type'  => 'text',
					'desc'  => __( 'Two letters. Generated from the name when left empty.', 'eethal-learning' ),
				),
				'colour'   => array(
					'label'   => __( 'Avatar colour', 'eethal-learning' ),
					'type'    => 'select',
					'options' => array(
						''     => __( 'Automatic', 'eethal-learning' ),
						'av-r' => __( 'Red', 'eethal-learning' ),
						'av-i' => __( 'Indigo', 'eethal-learning' ),
						'av-g' => __( 'Green', 'eethal-learning' ),
					),
				),
				'rating'   => array(
					'label'   => __( 'Star rating', 'eethal-learning' ),
					'type'    => 'select',
					'options' => array(
						'5' => '★★★★★',
						'4' => '★★★★',
						'3' => '★★★',
						'2' => '★★',
						'1' => '★',
						'0' => __( 'Hide stars', 'eethal-learning' ),
					),
				),
			),
		),
		'eethal_professional' => array(
			'title'  => __( 'Professional Details', 'eethal-learning' ),
			'fields' => array(
				'mobile'      => array(
					'label' => __( 'Mobile number', 'eethal-learning' ),
					'type'  => 'text',
				),
				'email'       => array(
					'label' => __( 'Email ID', 'eethal-learning' ),
					'type'  => 'text',
				),
				'district'    => array(
					'label' => __( 'Current district', 'eethal-learning' ),
					'type'  => 'text',
				),
				'experience'  => array(
					'label' => __( 'Total years of experience', 'eethal-learning' ),
					'type'  => 'text',
				),
				'company'     => array(
					'label' => __( 'Current company', 'eethal-learning' ),
					'type'  => 'text',
				),
				'designation' => array(
					'label' => __( 'Current designation', 'eethal-learning' ),
					'type'  => 'text',
				),
				'ctc'         => array(
					'label' => __( 'Current CTC', 'eethal-learning' ),
					'type'  => 'text',
				),
				'marital'     => array(
					'label'   => __( 'Marital status', 'eethal-learning' ),
					'type'    => 'select',
					'options' => array(
						''          => __( '— Not set —', 'eethal-learning' ),
						'Married'   => __( 'Married', 'eethal-learning' ),
						'Unmarried' => __( 'Unmarried', 'eethal-learning' ),
					),
				),
				'education'   => array(
					'label' => __( 'Education qualification', 'eethal-learning' ),
					'type'  => 'text',
				),
				'address'     => array(
					'label' => __( 'Current address', 'eethal-learning' ),
					'type'  => 'text',
				),
				'photo'       => array(
					'label' => __( 'Photo URL', 'eethal-learning' ),
					'type'  => 'url',
					'desc'  => __( 'Image URL or Google Drive link.', 'eethal-learning' ),
				),
				'verified'    => array(
					'label'   => __( 'Verified', 'eethal-learning' ),
					'type'    => 'select',
					'options' => array(
						'0' => __( 'No', 'eethal-learning' ),
						'1' => __( 'Yes', 'eethal-learning' ),
					),
				),
			),
		),
		'eethal_student'      => array(
			'title'  => __( 'Student Details', 'eethal-learning' ),
			'fields' => array(
				'mobile'         => array(
					'label' => __( 'Mobile number', 'eethal-learning' ),
					'type'  => 'text',
				),
				'email'          => array(
					'label' => __( 'Email ID', 'eethal-learning' ),
					'type'  => 'text',
				),
				'district'       => array(
					'label' => __( 'Current district', 'eethal-learning' ),
					'type'  => 'text',
				),
				'specialization' => array(
					'label' => __( 'Specialization', 'eethal-learning' ),
					'type'  => 'text',
				),
				'grad_year'      => array(
					'label' => __( 'Year of graduation', 'eethal-learning' ),
					'type'  => 'text',
				),
				'marital'        => array(
					'label'   => __( 'Marital status', 'eethal-learning' ),
					'type'    => 'select',
					'options' => array(
						''          => __( '— Not set —', 'eethal-learning' ),
						'Married'   => __( 'Married', 'eethal-learning' ),
						'Unmarried' => __( 'Unmarried', 'eethal-learning' ),
					),
				),
				'education'      => array(
					'label' => __( 'Education qualification', 'eethal-learning' ),
					'type'  => 'text',
				),
				'address'        => array(
					'label' => __( 'Current address', 'eethal-learning' ),
					'type'  => 'text',
				),
				'photo'          => array(
					'label' => __( 'Photo URL', 'eethal-learning' ),
					'type'  => 'url',
					'desc'  => __( 'Image URL or Google Drive link.', 'eethal-learning' ),
				),
			),
		),
	);
}

/**
 * Register the meta boxes.
 */
function eethal_add_meta_boxes() {
	foreach ( eethal_meta_fields() as $post_type => $box ) {
		add_meta_box(
			'eethal_' . $post_type . '_details',
			$box['title'],
			'eethal_render_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'eethal_add_meta_boxes' );

/**
 * Render a meta box.
 *
 * @param WP_Post $post Current post.
 */
function eethal_render_meta_box( $post ) {
	$all = eethal_meta_fields();
	if ( empty( $all[ $post->post_type ] ) ) {
		return;
	}

	wp_nonce_field( 'eethal_save_meta', 'eethal_meta_nonce' );

	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $all[ $post->post_type ]['fields'] as $key => $field ) {
		$id    = 'eethal_' . $key;
		$value = get_post_meta( $post->ID, '_eethal_' . $key, true );

		echo '<tr>';
		printf( '<th scope="row"><label for="%1$s">%2$s</label></th>', esc_attr( $id ), esc_html( $field['label'] ) );
		echo '<td>';

		if ( 'select' === $field['type'] ) {
			printf( '<select id="%1$s" name="%1$s">', esc_attr( $id ) );
			foreach ( $field['options'] as $opt_value => $opt_label ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $opt_value ),
					selected( (string) $value, (string) $opt_value, false ),
					esc_html( $opt_label )
				);
			}
			echo '</select>';
		} else {
			printf(
				'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="regular-text">',
				esc_attr( 'url' === $field['type'] ? 'url' : 'text' ),
				esc_attr( $id ),
				esc_attr( $value )
			);
		}

		if ( ! empty( $field['desc'] ) ) {
			printf( '<p class="description">%s</p>', wp_kses( $field['desc'], array( 'code' => array() ) ) );
		}

		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Persist meta box values.
 *
 * @param int $post_id Post ID.
 */
function eethal_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['eethal_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['eethal_meta_nonce'] ) ), 'eethal_save_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$all       = eethal_meta_fields();
	$post_type = get_post_type( $post_id );
	if ( empty( $all[ $post_type ] ) ) {
		return;
	}

	foreach ( $all[ $post_type ]['fields'] as $key => $field ) {
		$input = 'eethal_' . $key;
		if ( ! isset( $_POST[ $input ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $input ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( 'url' === $field['type'] ) {
			$value = esc_url_raw( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}
		// Talent Directory filter fields reuse an existing spelling that differs only by spaces, dots or case.
		if ( function_exists( 'eethal_td_existing_spelling' ) && in_array( $key, array( 'district', 'education', 'specialization', 'designation', 'company', 'grad_year' ), true ) ) {
			$value = eethal_td_existing_spelling( $key, eethal_td_tidy_filter_value( $key, $value ), $post_id );
		}

		if ( '' === $value ) {
			delete_post_meta( $post_id, '_eethal_' . $key );
		} else {
			update_post_meta( $post_id, '_eethal_' . $key, $value );
		}
	}
}
add_action( 'save_post', 'eethal_save_meta' );
