<?php
/**
 * Enroll Now: the course application form (replaces the Batch-8 Google Form).
 *
 * Anyone can apply on the Enroll Now page (/enroll/). Each application is saved as an
 * eethal_enrollment post tagged with the current batch, and the site admin gets an
 * email. Directory admins see the applications in the app's "Enrollments" view.
 * Every "Enroll Now" button on the site opens this page (see eethal_enroll_url()).
 *
 * All of the form's wording (heading, questions, choices, thank-you message) is edited
 * in Appearance → Customize → Eethal Front Page → Enroll Now Form; the app gets it
 * from eethal_enroll_texts(). The React code is in assets/talent-directory/src/enroll/.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EETHAL_ENROLL_TYPE', 'eethal_enrollment' );

/**
 * A text setting from Customizer → Eethal Front Page → Enroll Now Form, with {batch} filled in.
 *
 * @param string $key Setting key.
 * @return string
 */
function eethal_enroll_opt( $key ) {
	return str_replace( '{batch}', eethal_enroll_batch(), trim( (string) eethal_opt( $key ) ) );
}

/**
 * A one-choice-per-line setting as a list, falling back to the default if it was emptied.
 *
 * @param string $key Setting key.
 * @return string[]
 */
function eethal_enroll_choices( $key ) {
	$items = eethal_lines( $key );
	return $items ? $items : array_values( array_filter( array_map( 'trim', explode( "\n", eethal_defaults()[ $key ] ) ) ) );
}

/**
 * "Current Status" choices.
 *
 * @return string[]
 */
function eethal_enroll_statuses() {
	return eethal_enroll_choices( 'enroll_statuses' );
}

/**
 * The form's questions as authored in the Customizer ("Label | hint"), keyed by field.
 *
 * @return array Field => {label, hint}.
 */
function eethal_enroll_questions() {
	$keys      = array(
		'name'       => 'enroll_q_name',
		'email'      => 'enroll_q_email',
		'mobile'     => 'enroll_q_mobile',
		'dob'        => 'enroll_q_dob',
		'district'   => 'enroll_q_district',
		'referredBy' => 'enroll_q_referred',
		'status'     => 'enroll_q_status',
		'degree'     => 'enroll_q_degree',
		'college'    => 'enroll_q_college',
		'passedOut'  => 'enroll_q_passed',
	);
	$defaults  = eethal_defaults();
	$questions = array();
	foreach ( $keys as $field => $key ) {
		$raw   = eethal_enroll_opt( $key );
		$parts = array_map( 'trim', explode( '|', '' !== $raw ? $raw : $defaults[ $key ], 2 ) );

		$questions[ $field ] = array(
			'label' => $parts[0],
			'hint'  => $parts[1] ?? '',
		);
	}
	return $questions;
}

/**
 * Everything the Enroll Now page shows, as authored in the Customizer. Passed to the app
 * as EETHAL_TD.enroll.
 *
 * @return array
 */
function eethal_enroll_texts() {
	return array(
		'batch'       => eethal_enroll_batch(),
		'badge'       => eethal_enroll_opt( 'enroll_badge' ),
		'title'       => eethal_enroll_opt( 'enroll_title' ),
		'intro'       => eethal_enroll_opt( 'enroll_intro' ),
		'note'        => eethal_enroll_opt( 'enroll_note' ),
		'sections'    => array( eethal_enroll_opt( 'enroll_section_1' ), eethal_enroll_opt( 'enroll_section_2' ) ),
		'questions'   => eethal_enroll_questions(),
		'statuses'    => eethal_enroll_statuses(),
		'degrees'     => eethal_enroll_choices( 'enroll_degrees' ),
		'degreeOther' => eethal_enroll_opt( 'enroll_degree_other' ),
		'button'      => eethal_enroll_opt( 'enroll_button' ),
		'thanksTitle' => eethal_enroll_opt( 'enroll_thanks_title' ),
		'thanksText'  => eethal_enroll_opt( 'enroll_thanks_text' ),
	);
}

/**
 * The form's fields (meta keys are "_eethal_enroll_" + field).
 *
 * @return string[]
 */
function eethal_enroll_fields() {
	return array( 'email', 'mobile', 'referredBy', 'status', 'degree', 'passedOut', 'dob', 'district', 'college', 'batch' );
}

/**
 * The batch new applications join, e.g. "Batch 8" (Customizer → Enroll Now Form).
 *
 * @return string
 */
function eethal_enroll_batch() {
	$batch = trim( (string) eethal_opt( 'enroll_batch' ) );
	return '' !== $batch ? $batch : eethal_defaults()['enroll_batch'];
}

/**
 * Register the application post type. Applications are viewed in the app, not in wp-admin.
 */
function eethal_enroll_register_type() {
	register_post_type(
		EETHAL_ENROLL_TYPE,
		array(
			'labels'   => array(
				'name'          => esc_html__( 'Enrollments', 'eethal-learning' ),
				'singular_name' => esc_html__( 'Enrollment', 'eethal-learning' ),
			),
			'public'   => false,
			'show_ui'  => false,
			'supports' => array( 'title' ),
		)
	);
}
add_action( 'init', 'eethal_enroll_register_type' );

/**
 * Register /wp-json/eethal/v1/enrollments.
 */
function eethal_enroll_register_routes() {
	register_rest_route(
		'eethal/v1',
		'/enrollments',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => 'eethal_td_can_manage',
				'callback'            => 'eethal_enroll_rest_list',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => 'eethal_enroll_rest_submit',
			),
		)
	);

	register_rest_route(
		'eethal/v1',
		'/enrollments/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'permission_callback' => 'eethal_td_can_manage',
			'callback'            => 'eethal_enroll_rest_delete',
		)
	);
}
add_action( 'rest_api_init', 'eethal_enroll_register_routes' );

/**
 * Sanitize a submitted application and check it, matching the checks in the app's form.
 *
 * @param array $body JSON body.
 * @return array|WP_Error Clean values, plus "name".
 */
function eethal_enroll_clean( array $body ) {
	$text = function ( $key ) use ( $body ) {
		return sanitize_text_field( (string) ( $body[ $key ] ?? '' ) );
	};
	$data = array(
		'name'       => $text( 'name' ),
		'email'      => sanitize_email( (string) ( $body['email'] ?? '' ) ),
		'mobile'     => preg_replace( '/\D/', '', $text( 'mobile' ) ),
		'referredBy' => $text( 'referredBy' ),
		'status'     => $text( 'status' ),
		'degree'     => $text( 'degree' ),
		'passedOut'  => $text( 'passedOut' ),
		'dob'        => $text( 'dob' ),
		'district'   => $text( 'district' ),
		'college'    => $text( 'college' ),
	);

	// Messages name the question as it's worded on the form (keep in step with enroll/validate.js).
	$questions = eethal_enroll_questions();
	$errors    = array();
	foreach ( array( 'name', 'referredBy', 'degree', 'district', 'college' ) as $field ) {
		if ( '' === $data[ $field ] ) {
			/* translators: %s: question label, e.g. "Name". */
			$errors[ $field ] = sprintf( __( '%s is required.', 'eethal-learning' ), $questions[ $field ]['label'] );
		}
	}
	if ( ! is_email( $data['email'] ) ) {
		$errors['email'] = __( 'Enter a valid email address.', 'eethal-learning' );
	}
	if ( ! preg_match( '/^\d{10}$/', $data['mobile'] ) ) {
		$errors['mobile'] = __( 'Enter a valid 10-digit mobile number.', 'eethal-learning' );
	}
	if ( ! in_array( $data['status'], eethal_enroll_statuses(), true ) ) {
		$errors['status'] = __( 'Choose one of the options.', 'eethal-learning' );
	}
	// A typed-in degree is only allowed while the "Other" choice is offered.
	if ( '' !== $data['degree'] && '' === eethal_enroll_opt( 'enroll_degree_other' ) && ! in_array( $data['degree'], eethal_enroll_choices( 'enroll_degrees' ), true ) ) {
		$errors['degree'] = __( 'Choose one of the options.', 'eethal-learning' );
	}
	$year = (int) $data['passedOut'];
	if ( ! preg_match( '/^\d{4}$/', $data['passedOut'] ) || $year < 1970 || $year > (int) gmdate( 'Y' ) + 6 ) {
		$errors['passedOut'] = __( 'Enter the year as 4 digits, e.g. 2024.', 'eethal-learning' );
	}
	$dob = DateTime::createFromFormat( '!Y-m-d', $data['dob'] );
	if ( ! $dob || $dob->format( 'Y-m-d' ) !== $data['dob'] || $dob > new DateTime( 'today' ) ) {
		$errors['dob'] = __( 'Enter a valid date of birth.', 'eethal-learning' );
	}

	if ( $errors ) {
		return new WP_Error(
			'eethal_enroll_invalid',
			__( 'Please correct the highlighted fields.', 'eethal-learning' ),
			array(
				'status' => 400,
				'fields' => $errors,
			)
		);
	}
	return $data;
}

/**
 * POST enrollments: save an application from the Enroll Now page and email the admin.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function eethal_enroll_rest_submit( WP_REST_Request $request ) {
	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) {
		return new WP_Error( 'eethal_enroll_invalid_json', __( 'Invalid JSON body.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	// Honeypot, form token, links, captcha and hourly limit (inc/spam-guard.php).
	$guard = eethal_spam_guard( $body, 'enroll' );
	if ( true !== $guard ) {
		return $guard;
	}

	$data = eethal_enroll_clean( $body );
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	$data['batch'] = eethal_enroll_batch();

	// One application per person per batch.
	if ( eethal_enroll_already_applied( $data ) ) {
		return new WP_Error( 'eethal_enroll_duplicate', str_replace( '{batch}', $data['batch'], eethal_spam_msg( 'spam_msg_duplicate_enroll' ) ), array( 'status' => 409 ) );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => EETHAL_ENROLL_TYPE,
			'post_status' => 'publish',
			'post_title'  => $data['name'],
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'eethal_enroll_save_failed', $post_id->get_error_message(), array( 'status' => 500 ) );
	}
	foreach ( eethal_enroll_fields() as $field ) {
		update_post_meta( $post_id, '_eethal_enroll_' . $field, $data[ $field ] );
	}

	eethal_spam_count( 'enroll' );
	eethal_enroll_send_email( $post_id );
	eethal_td_log(
		'enroll_new',
		/* translators: 1: name, 2: batch. */
		sprintf( __( '“%1$s” applied for %2$s.', 'eethal-learning' ), $data['name'], $data['batch'] ),
		false,
		array(
			'type' => 'enrollments',
			'id'   => $post_id,
		)
	);

	$response = rest_ensure_response( array( 'success' => true ) );
	$response->set_status( 201 );
	return $response;
}

/**
 * Whether this email or mobile number already applied for the same batch.
 *
 * @param array $data Clean application, including "batch".
 * @return bool
 */
function eethal_enroll_already_applied( array $data ) {
	return (bool) get_posts(
		array(
			'post_type'   => EETHAL_ENROLL_TYPE,
			'post_status' => 'publish',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'   => '_eethal_enroll_batch',
					'value' => $data['batch'],
				),
				array(
					'relation' => 'OR',
					array(
						'key'   => '_eethal_enroll_email',
						'value' => $data['email'],
					),
					array(
						'key'   => '_eethal_enroll_mobile',
						'value' => $data['mobile'],
					),
				),
			),
		)
	);
}

/**
 * An application in the shape the app expects.
 *
 * @param WP_Post $post Application post.
 * @return array
 */
function eethal_enroll_record( $post ) {
	$record = array(
		'id'        => $post->ID,
		'name'      => html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ),
		'createdAt' => get_post_time( 'c', true, $post ),
	);
	foreach ( eethal_enroll_fields() as $field ) {
		$record[ $field ] = (string) get_post_meta( $post->ID, '_eethal_enroll_' . $field, true );
	}
	return $record;
}

/**
 * GET enrollments: every application, newest first.
 *
 * @return WP_REST_Response
 */
function eethal_enroll_rest_list() {
	$posts = get_posts(
		array(
			'post_type'   => EETHAL_ENROLL_TYPE,
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);
	return rest_ensure_response( array_map( 'eethal_enroll_record', $posts ) );
}

/**
 * DELETE enrollments/{id}: move an application to the trash.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function eethal_enroll_rest_delete( WP_REST_Request $request ) {
	$post = get_post( (int) $request['id'] );
	if ( ! $post || EETHAL_ENROLL_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'eethal_enroll_not_found', __( 'Application not found.', 'eethal-learning' ), array( 'status' => 404 ) );
	}
	wp_trash_post( $post->ID );
	return rest_ensure_response( array( 'deleted' => true ) );
}

/**
 * Email the site admin about a new application.
 *
 * @param int $post_id Application ID.
 * @return bool Whether wp_mail() accepted the message.
 */
function eethal_enroll_send_email( $post_id ) {
	$app = eethal_enroll_record( get_post( $post_id ) );

	/**
	 * Filters who is told about new applications. Defaults to the site admin email.
	 *
	 * @param string|string[] $to  Recipient address(es).
	 * @param array           $app The application.
	 */
	$to = apply_filters( 'eethal_enroll_notify_to', get_option( 'admin_email' ), $app );

	/* translators: 1: batch, 2: person's name. */
	$subject = sprintf( __( 'New %1$s application: %2$s', 'eethal-learning' ), $app['batch'], $app['name'] );
	return wp_mail( $to, $subject, eethal_enroll_email_html( $app ), array( 'Content-Type: text/html; charset=UTF-8' ) );
}

/**
 * HTML for the new application email, in the same style as the new entry email.
 *
 * @param array $app The application.
 * @return string
 */
function eethal_enroll_email_html( array $app ) {
	$site_name = get_bloginfo( 'name' );
	$list_url  = add_query_arg( 'view', 'enrollments', eethal_td_page_urls()['dashboard'] );
	// Rows use the questions as worded on the form.
	$rows = array();
	foreach ( eethal_enroll_questions() as $field => $question ) {
		if ( 'name' === $field ) {
			continue; // Already the email's heading.
		}
		$value = $app[ $field ];
		if ( 'mobile' === $field ) {
			$value = '+91 ' . $value;
		} elseif ( 'dob' === $field ) {
			$value = wp_date( 'j M Y', strtotime( $value ) );
		}
		$rows[ $question['label'] ] = $value;
	}

	ob_start();
	?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?php echo esc_html( $site_name ); ?></title></head>
<body style="margin:0;padding:0;background:#F2F3FA;font-family:Inter,Segoe UI,Helvetica,Arial,sans-serif;color:#1B1D36;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F3FA;padding:32px 12px;">
<tr><td align="center">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(27,29,54,0.08);">
		<tr><td bgcolor="#5B5FEF" style="background:#5B5FEF;background-image:linear-gradient(135deg,#5B5FEF 0%,#8B5CF6 55%,#EC4899 100%);padding:34px 36px 30px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
				<td style="font-size:18px;font-weight:700;color:#FFFFFF;"><?php echo esc_html( $site_name ); ?></td>
				<td align="right"><span style="display:inline-block;background:rgba(255,255,255,0.2);color:#FFFFFF;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 12px;border-radius:999px;"><?php echo esc_html( $app['batch'] ); ?></span></td>
			</tr></table>
			<div style="font-size:26px;line-height:1.3;font-weight:700;color:#FFFFFF;margin-top:26px;"><?php echo esc_html( sprintf( /* translators: %s: name. */ __( '%s applied to enroll', 'eethal-learning' ), $app['name'] ) ); ?></div>
			<div style="font-size:14px;line-height:1.6;color:rgba(255,255,255,0.88);margin-top:8px;"><?php esc_html_e( 'A new application came in through the Enroll Now form.', 'eethal-learning' ); ?></div>
		</td></tr>
		<tr><td style="padding:24px 36px 8px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E6E8F2;border-radius:12px;border-collapse:separate;overflow:hidden;">
				<?php
				$i = 0;
				foreach ( $rows as $row_label => $value ) :
					?>
				<tr style="background:<?php echo 0 === $i++ % 2 ? '#FFFFFF' : '#F8F9FD'; ?>;">
					<td style="padding:11px 16px;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#8A8DA8;width:38%;vertical-align:top;"><?php echo esc_html( $row_label ); ?></td>
					<td style="padding:11px 16px;font-size:14px;font-weight:600;color:#1B1D36;"><?php echo esc_html( $value ); ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</td></tr>
		<tr><td align="center" style="padding:26px 36px 30px;">
			<a href="<?php echo esc_url( $list_url ); ?>" style="display:inline-block;background:#5B5FEF;background-image:linear-gradient(135deg,#5B5FEF,#8B5CF6);color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:10px;"><?php esc_html_e( 'View Enrollments', 'eethal-learning' ); ?> &rarr;</a>
			<div style="font-size:12.5px;color:#8A8DA8;margin-top:12px;"><?php echo esc_html( sprintf( /* translators: %s: date and time. */ __( 'Submitted on %s', 'eethal-learning' ), wp_date( 'j M Y, g:i a', strtotime( $app['createdAt'] ) ) ) ); ?></div>
		</td></tr>
	</table>
</td></tr>
</table>
</body>
</html>
	<?php
	return ob_get_clean();
}
