<?php
/**
 * Talent Directory: public entry form submissions.
 *
 * Anyone can submit their own details on the Entry Form page (/entry-form/). Each
 * submission is saved as an eethal_entry post with status "pending", and the site admin
 * gets an email. Directory admins review entries in the app's "New Entries" view:
 * approving one creates the Working Professional or Student profile, rejecting it
 * only marks it rejected.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EETHAL_TD_ENTRY_TYPE', 'eethal_entry' );

/**
 * Register the entry post type. Entries are managed in the app, not in wp-admin.
 */
function eethal_td_register_entry_type() {
	register_post_type(
		EETHAL_TD_ENTRY_TYPE,
		array(
			'labels'   => array(
				'name'          => esc_html__( 'Entries', 'eethal-learning' ),
				'singular_name' => esc_html__( 'Entry', 'eethal-learning' ),
			),
			'public'   => false,
			'show_ui'  => false,
			'supports' => array( 'title' ),
		)
	);
}
add_action( 'init', 'eethal_td_register_entry_type' );

/**
 * Register the entry routes under /wp-json/eethal/v1/entries.
 */
function eethal_td_register_entry_routes() {
	$namespace = 'eethal/v1';

	register_rest_route(
		$namespace,
		'/entries',
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => 'eethal_td_can_manage',
				'callback'            => 'eethal_td_rest_list_entries',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => 'eethal_td_rest_submit_entry',
			),
		)
	);

	register_rest_route(
		$namespace,
		'/entries/(?P<id>\d+)/(?P<decision>approve|reject)',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'eethal_td_can_manage',
			'callback'            => 'eethal_td_rest_review_entry',
		)
	);

	register_rest_route(
		$namespace,
		'/entries/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::DELETABLE,
			'permission_callback' => 'eethal_td_can_manage',
			'callback'            => 'eethal_td_rest_delete_entry',
		)
	);

	register_rest_route(
		$namespace,
		'/entries/(?P<id>\d+)/restore',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'eethal_td_can_manage',
			'callback'            => 'eethal_td_rest_restore_entry',
		)
	);
}
add_action( 'rest_api_init', 'eethal_td_register_entry_routes' );

/**
 * Fields a person may submit (photo and the rest, but never "verified").
 *
 * @param string $type "professional" or "student".
 * @return string[]
 */
function eethal_td_entry_fields( $type ) {
	return array_values( array_diff( eethal_td_fields( $type ), array( 'verified' ) ) );
}

/**
 * Check the required fields, matching the checks in the app's form.
 *
 * @param string $type "professional" or "student".
 * @param array  $data Sanitized values, plus "name".
 * @return true|WP_Error
 */
function eethal_td_validate_entry( $type, array $data ) {
	$errors = array();
	if ( '' === ( $data['name'] ?? '' ) ) {
		$errors['name'] = __( 'Full name is required.', 'eethal-learning' );
	}
	if ( ! preg_match( '/^\d{10}$/', $data['mobile'] ?? '' ) ) {
		$errors['mobile'] = __( 'Enter a valid 10-digit mobile number.', 'eethal-learning' );
	}
	if ( ! is_email( $data['email'] ?? '' ) ) {
		$errors['email'] = __( 'Enter a valid email address.', 'eethal-learning' );
	}
	$required = array(
		'district'  => __( 'Current district is required.', 'eethal-learning' ),
		'education' => __( 'Education qualification is required.', 'eethal-learning' ),
	);
	if ( 'professional' === $type ) {
		$required += array(
			'experience'  => __( 'Experience is required.', 'eethal-learning' ),
			'company'     => __( 'Current company is required.', 'eethal-learning' ),
			'designation' => __( 'Current designation is required.', 'eethal-learning' ),
		);
	} else {
		$required += array(
			'specialization' => __( 'Specialization is required.', 'eethal-learning' ),
			'gradYear'       => __( 'Year of graduation is required.', 'eethal-learning' ),
		);
	}
	foreach ( $required as $field => $message ) {
		if ( '' === ( $data[ $field ] ?? '' ) ) {
			$errors[ $field ] = $message;
		}
	}

	if ( $errors ) {
		return new WP_Error(
			'eethal_td_invalid_entry',
			__( 'Please correct the highlighted fields.', 'eethal-learning' ),
			array(
				'status' => 400,
				'fields' => $errors,
			)
		);
	}
	return true;
}

/**
 * POST entries: save a submission from the public Entry Form and email the admin.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function eethal_td_rest_submit_entry( WP_REST_Request $request ) {
	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) {
		return new WP_Error( 'eethal_td_invalid_json', __( 'Invalid JSON body.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	// Bots fill the hidden "website" field; pretend it worked so they don't retry.
	if ( ! empty( $body['website'] ) ) {
		return rest_ensure_response( array( 'success' => true ) );
	}

	// At most 5 submissions per hour from one address.
	$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$limit_key = 'eethal_td_entry_' . md5( $ip );
	$count     = (int) get_transient( $limit_key );
	if ( $count >= 5 ) {
		return new WP_Error( 'eethal_td_too_many', __( 'Too many submissions. Please try again in an hour.', 'eethal-learning' ), array( 'status' => 429 ) );
	}

	$type = ( $body['type'] ?? '' ) === 'student' ? 'student' : 'professional';
	$data = array( 'name' => sanitize_text_field( (string) ( $body['name'] ?? '' ) ) );
	foreach ( eethal_td_entry_fields( $type ) as $field ) {
		if ( 'photo' === $field ) {
			continue; // Saved after validation, so a rejected form doesn't upload the image.
		}
		$value = eethal_td_sanitize_field( $field, $body[ $field ] ?? '' );
		if ( is_wp_error( $value ) ) {
			return $value;
		}
		$data[ $field ] = eethal_td_tidy_filter_value( $field, $value );
	}

	$valid = eethal_td_validate_entry( $type, $data );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	if ( ! empty( $body['photo'] ) ) {
		$photo = eethal_td_photo_value( (string) $body['photo'] );
		if ( is_wp_error( $photo ) ) {
			return $photo;
		}
		$data['photo'] = $photo;
	}

	$entry_id = wp_insert_post(
		array(
			'post_type'   => EETHAL_TD_ENTRY_TYPE,
			'post_status' => 'publish',
			'post_title'  => $data['name'],
		),
		true
	);
	if ( is_wp_error( $entry_id ) ) {
		return new WP_Error( 'eethal_td_save_failed', $entry_id->get_error_message(), array( 'status' => 500 ) );
	}

	update_post_meta( $entry_id, '_eethal_entry_type', $type );
	update_post_meta( $entry_id, '_eethal_entry_status', 'pending' );
	foreach ( eethal_td_entry_fields( $type ) as $field ) {
		if ( isset( $data[ $field ] ) && '' !== $data[ $field ] ) {
			update_post_meta( $entry_id, eethal_td_meta_key( $field ), $data[ $field ] );
		}
	}

	set_transient( $limit_key, $count + 1, HOUR_IN_SECONDS );
	eethal_td_send_entry_email( $entry_id );
	eethal_td_log(
		'entry_new',
		'student' === $type
			/* translators: %s: name. */
			? sprintf( __( 'New student entry from “%s” is waiting for review.', 'eethal-learning' ), $data['name'] )
			/* translators: %s: name. */
			: sprintf( __( 'New professional entry from “%s” is waiting for review.', 'eethal-learning' ), $data['name'] ),
		false,
		array(
			'type' => 'entries',
			'id'   => $entry_id,
		)
	);

	$response = rest_ensure_response( array( 'success' => true ) );
	$response->set_status( 201 );
	return $response;
}

/**
 * An entry in the shape the app expects.
 *
 * @param WP_Post $post Entry post.
 * @return array
 */
function eethal_td_entry_record( $post ) {
	$type   = 'student' === get_post_meta( $post->ID, '_eethal_entry_type', true ) ? 'student' : 'professional';
	$record = array(
		'id'     => $post->ID,
		'type'   => $type,
		'status' => get_post_meta( $post->ID, '_eethal_entry_status', true ) ?: 'pending',
		'name'   => html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ),
	);
	foreach ( eethal_td_entry_fields( $type ) as $field ) {
		$record[ $field ] = (string) get_post_meta( $post->ID, eethal_td_meta_key( $field ), true );
	}
	$record['createdAt']  = get_post_time( 'c', true, $post );
	$record['reviewedAt'] = (string) get_post_meta( $post->ID, '_eethal_entry_reviewed_at', true );
	$record['reviewedBy'] = (string) get_post_meta( $post->ID, '_eethal_entry_reviewed_by', true );
	$record['profileId']  = (int) get_post_meta( $post->ID, '_eethal_entry_profile', true );
	$record['deleted']    = '1' === get_post_meta( $post->ID, '_eethal_entry_deleted', true );
	$record['deletedAt']  = (string) get_post_meta( $post->ID, '_eethal_entry_deleted_at', true );
	$record['deletedBy']  = (string) get_post_meta( $post->ID, '_eethal_entry_deleted_by', true );
	$record['duplicate']  = 'pending' === $record['status'] ? eethal_td_find_profile_by_email( $record['email'] ) : null;
	return $record;
}

/**
 * An existing profile with this email, so the admin can spot a repeat submission.
 *
 * @param string $email Email address.
 * @return array|null {type, id, name} or null.
 */
function eethal_td_find_profile_by_email( $email ) {
	if ( '' === $email ) {
		return null;
	}
	foreach ( array( 'professional', 'student' ) as $type ) {
		$ids = get_posts(
			array(
				'post_type'   => eethal_td_post_type( $type ),
				'post_status' => 'publish',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => eethal_td_meta_key( 'email' ), // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => $email, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		if ( $ids ) {
			return array(
				'type' => $type,
				'id'   => $ids[0],
				'name' => html_entity_decode( get_the_title( $ids[0] ), ENT_QUOTES, 'UTF-8' ),
			);
		}
	}
	return null;
}

/**
 * GET entries: every entry that isn't soft-deleted, newest first.
 * Deleted entries stay in the database but are never shown in the dashboard.
 *
 * @return WP_REST_Response
 */
function eethal_td_rest_list_entries() {
	$posts = get_posts(
		array(
			'post_type'   => EETHAL_TD_ENTRY_TYPE,
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array(
					'key'     => '_eethal_entry_deleted',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => '_eethal_entry_deleted',
					'value' => '1',
					'compare' => '!=',
				),
			),
		)
	);
	return rest_ensure_response( array_map( 'eethal_td_entry_record', $posts ) );
}

/**
 * Get an entry post, or a 404 error.
 *
 * @param int $entry_id Entry ID.
 * @return WP_Post|WP_Error
 */
function eethal_td_get_entry( $entry_id ) {
	$post = get_post( $entry_id );
	if ( ! $post || EETHAL_TD_ENTRY_TYPE !== $post->post_type ) {
		return new WP_Error( 'eethal_td_not_found', __( 'Entry not found.', 'eethal-learning' ), array( 'status' => 404 ) );
	}
	return $post;
}

/**
 * POST entries/{id}/approve or entries/{id}/reject.
 *
 * Approving creates the profile in Working Professionals or Students.
 *
 * @param WP_REST_Request $request Request.
 * @return array|WP_Error {entry, profile} where profile is the new profile (approve only).
 */
function eethal_td_rest_review_entry( WP_REST_Request $request ) {
	$post = eethal_td_get_entry( (int) $request['id'] );
	if ( is_wp_error( $post ) ) {
		return $post;
	}
	$entry = eethal_td_entry_record( $post );
	if ( 'pending' !== $entry['status'] ) {
		return new WP_Error( 'eethal_td_already_reviewed', __( 'This entry has already been reviewed.', 'eethal-learning' ), array( 'status' => 409 ) );
	}

	$profile = null;
	if ( 'approve' === $request['decision'] ) {
		$fields = array( 'name' => $entry['name'] );
		foreach ( eethal_td_entry_fields( $entry['type'] ) as $field ) {
			$fields[ $field ] = $entry[ $field ];
		}
		$profile = eethal_td_save_profile( $entry['type'], $fields );
		if ( is_wp_error( $profile ) ) {
			return $profile;
		}
		update_post_meta( $post->ID, '_eethal_entry_profile', $profile['id'] );
	}

	update_post_meta( $post->ID, '_eethal_entry_status', 'approve' === $request['decision'] ? 'approved' : 'rejected' );
	update_post_meta( $post->ID, '_eethal_entry_reviewed_at', gmdate( 'c' ) );
	update_post_meta( $post->ID, '_eethal_entry_reviewed_by', wp_get_current_user()->display_name );

	if ( $profile ) {
		// Visitors just see a new profile; admins see that it came from an approved entry.
		eethal_td_log(
			'entry_approved',
			eethal_td_added_text( $entry['type'], $entry['name'] ),
			true,
			array(
				'type' => $entry['type'],
				'id'   => $profile['id'],
			),
			/* translators: 1: name, 2: list name, 3: admin name. */
			sprintf( __( 'Entry from “%1$s” was approved and added to %2$s by %3$s.', 'eethal-learning' ), $entry['name'], eethal_td_list_name( $entry['type'] ), eethal_td_actor() )
		);
	} else {
		eethal_td_log(
			'entry_rejected',
			/* translators: 1: name, 2: admin name. */
			sprintf( __( 'Entry from “%1$s” was rejected by %2$s.', 'eethal-learning' ), $entry['name'], eethal_td_actor() ),
			false,
			array(
				'type' => 'entries',
				'id'   => $post->ID,
			)
		);
	}

	return array(
		'entry'   => eethal_td_entry_record( get_post( $post->ID ) ),
		'profile' => $profile,
	);
}

/**
 * DELETE entries/{id}: soft delete. The entry stays in the database with
 * _eethal_entry_deleted = '1' (plus who and when), so it can be checked or restored later.
 * An approved entry's profile is not touched.
 *
 * @param WP_REST_Request $request Request.
 * @return array|WP_Error The updated entry.
 */
function eethal_td_rest_delete_entry( WP_REST_Request $request ) {
	$post = eethal_td_get_entry( (int) $request['id'] );
	if ( is_wp_error( $post ) ) {
		return $post;
	}
	if ( 'pending' === eethal_td_entry_record( $post )['status'] ) {
		return new WP_Error( 'eethal_td_entry_pending', __( 'Approve or reject this entry before deleting it.', 'eethal-learning' ), array( 'status' => 409 ) );
	}
	update_post_meta( $post->ID, '_eethal_entry_deleted', '1' );
	update_post_meta( $post->ID, '_eethal_entry_deleted_at', gmdate( 'c' ) );
	update_post_meta( $post->ID, '_eethal_entry_deleted_by', wp_get_current_user()->display_name );
	eethal_td_log(
		'entry_deleted',
		/* translators: 1: name, 2: admin name. */
		sprintf( __( 'Entry from “%1$s” was deleted by %2$s.', 'eethal-learning' ), html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ), eethal_td_actor() )
	);
	return eethal_td_entry_record( get_post( $post->ID ) );
}

/**
 * POST entries/{id}/restore: undo a soft delete.
 *
 * @param WP_REST_Request $request Request.
 * @return array|WP_Error The updated entry.
 */
function eethal_td_rest_restore_entry( WP_REST_Request $request ) {
	$post = eethal_td_get_entry( (int) $request['id'] );
	if ( is_wp_error( $post ) ) {
		return $post;
	}
	update_post_meta( $post->ID, '_eethal_entry_deleted', '0' );
	delete_post_meta( $post->ID, '_eethal_entry_deleted_at' );
	delete_post_meta( $post->ID, '_eethal_entry_deleted_by' );
	return eethal_td_entry_record( get_post( $post->ID ) );
}

/* ------------------------------------------------------------------------- *
 * Admin email
 * ------------------------------------------------------------------------- */

/**
 * Email the site admin that a new entry is waiting for review.
 *
 * @param int $entry_id Entry ID.
 * @return bool Whether wp_mail() accepted the message.
 */
function eethal_td_send_entry_email( $entry_id ) {
	$entry = eethal_td_entry_record( get_post( $entry_id ) );
	$label = 'student' === $entry['type'] ? __( 'Student', 'eethal-learning' ) : __( 'Working Professional', 'eethal-learning' );

	/**
	 * Filters who is told about new entries. Defaults to the site admin email.
	 *
	 * @param string|string[] $to    Recipient address(es).
	 * @param array           $entry The entry.
	 */
	$to = apply_filters( 'eethal_td_entry_notify_to', get_option( 'admin_email' ), $entry );

	/* translators: 1: Student or Working Professional, 2: person's name. */
	$subject = sprintf( __( 'New %1$s entry: %2$s', 'eethal-learning' ), $label, $entry['name'] );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	return wp_mail( $to, $subject, eethal_td_entry_email_html( $entry, $label ), $headers );
}

/**
 * Newsletter-style HTML for the new entry email. Table layout and inline styles,
 * so it renders the same in Gmail, Outlook and phone mail apps.
 *
 * @param array  $entry The entry.
 * @param string $label "Student" or "Working Professional".
 * @return string
 */
function eethal_td_entry_email_html( array $entry, $label ) {
	$is_student = 'student' === $entry['type'];
	$accent     = $is_student ? '#0FA981' : '#5B5FEF';
	$accent_bg  = $is_student ? '#E4F7F1' : '#EEF0FF';
	$review_url = add_query_arg( 'view', 'entries', eethal_td_page_urls()['dashboard'] );
	$site_name  = get_bloginfo( 'name' );
	$na         = __( 'NA', 'eethal-learning' );

	$rows = array(
		__( 'Mobile', 'eethal-learning' )     => '+91 ' . $entry['mobile'],
		__( 'Email', 'eethal-learning' )      => $entry['email'],
		__( 'Batch No', 'eethal-learning' )   => $entry['batch'],
		__( 'District', 'eethal-learning' )   => $entry['district'],
		__( 'Education', 'eethal-learning' )  => $entry['education'],
	);
	if ( $is_student ) {
		$rows[ __( 'Specialization', 'eethal-learning' ) ]     = $entry['specialization'];
		$rows[ __( 'Year of Graduation', 'eethal-learning' ) ] = $entry['gradYear'];
	} else {
		$rows[ __( 'Company', 'eethal-learning' ) ]     = $entry['company'];
		$rows[ __( 'Designation', 'eethal-learning' ) ] = $entry['designation'];
		$rows[ __( 'Experience', 'eethal-learning' ) ]  = '' !== $entry['experience'] ? $entry['experience'] . ' ' . __( 'years', 'eethal-learning' ) : '';
		$rows[ __( 'Current CTC', 'eethal-learning' ) ] = $entry['ctc'];
	}
	$rows[ __( 'Marital Status', 'eethal-learning' ) ] = $entry['marital'];
	$rows[ __( 'Address', 'eethal-learning' ) ]        = $entry['address'];

	$initials = '';
	foreach ( array_slice( preg_split( '/\s+/', trim( $entry['name'] ) ), 0, 2 ) as $word ) {
		$initials .= strtoupper( mb_substr( $word, 0, 1 ) );
	}
	$submitted = wp_date( 'j M Y, g:i a', strtotime( $entry['createdAt'] ) );

	ob_start();
	?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?php echo esc_html( $site_name ); ?></title></head>
<body style="margin:0;padding:0;background:#F2F3FA;font-family:Inter,Segoe UI,Helvetica,Arial,sans-serif;color:#1B1D36;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;"><?php echo esc_html( sprintf( /* translators: 1: name, 2: Student or Working Professional. */ __( '%1$s submitted a %2$s entry. Review and approve it.', 'eethal-learning' ), $entry['name'], $label ) ); ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F3FA;padding:32px 12px;">
<tr><td align="center">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(27,29,54,0.08);">

		<!-- Header band -->
		<tr><td bgcolor="#5B5FEF" style="background:#5B5FEF;background-image:linear-gradient(135deg,#5B5FEF 0%,#8B5CF6 55%,#EC4899 100%);padding:34px 36px 30px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
				<td style="font-size:18px;font-weight:700;color:#FFFFFF;letter-spacing:.2px;"><?php echo esc_html( $site_name ); ?></td>
				<td align="right"><span style="display:inline-block;background:rgba(255,255,255,0.2);color:#FFFFFF;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 12px;border-radius:999px;"><?php esc_html_e( 'New Entry', 'eethal-learning' ); ?></span></td>
			</tr></table>
			<div style="font-size:26px;line-height:1.3;font-weight:700;color:#FFFFFF;margin-top:26px;"><?php esc_html_e( 'A new entry is waiting for your review', 'eethal-learning' ); ?></div>
			<div style="font-size:14px;line-height:1.6;color:rgba(255,255,255,0.88);margin-top:8px;"><?php esc_html_e( 'Someone just filled in the Entry Form. Check the details below, then approve or reject it from the admin dashboard.', 'eethal-learning' ); ?></div>
		</td></tr>

		<!-- Person -->
		<tr><td style="padding:28px 36px 8px;">
			<table role="presentation" cellpadding="0" cellspacing="0"><tr>
				<td valign="middle" style="width:56px;">
					<div style="width:56px;height:56px;border-radius:50%;background:<?php echo esc_attr( $accent ); ?>;color:#FFFFFF;font-size:20px;font-weight:700;line-height:56px;text-align:center;"><?php echo esc_html( $initials ); ?></div>
				</td>
				<td valign="middle" style="padding-left:16px;">
					<div style="font-size:20px;font-weight:700;color:#1B1D36;"><?php echo esc_html( $entry['name'] ); ?></div>
					<span style="display:inline-block;margin-top:6px;background:<?php echo esc_attr( $accent_bg ); ?>;color:<?php echo esc_attr( $accent ); ?>;font-size:12px;font-weight:700;padding:4px 10px;border-radius:999px;"><?php echo esc_html( $label ); ?></span>
				</td>
			</tr></table>
		</td></tr>

		<!-- Details -->
		<tr><td style="padding:16px 36px 8px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E6E8F2;border-radius:12px;border-collapse:separate;overflow:hidden;">
				<?php
				$i = 0;
				foreach ( $rows as $row_label => $value ) :
					$empty = '' === trim( (string) $value );
					?>
				<tr style="background:<?php echo 0 === $i++ % 2 ? '#FFFFFF' : '#F8F9FD'; ?>;">
					<td style="padding:11px 16px;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#8A8DA8;width:38%;vertical-align:top;"><?php echo esc_html( $row_label ); ?></td>
					<td style="padding:11px 16px;font-size:14px;font-weight:600;color:<?php echo $empty ? '#A3A6BD' : '#1B1D36'; ?>;<?php echo $empty ? 'font-style:italic;font-weight:500;' : ''; ?>"><?php echo esc_html( $empty ? $na : $value ); ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</td></tr>

		<!-- Call to action -->
		<tr><td align="center" style="padding:26px 36px 10px;">
			<a href="<?php echo esc_url( $review_url ); ?>" style="display:inline-block;background:#5B5FEF;background-image:linear-gradient(135deg,#5B5FEF,#8B5CF6);color:#FFFFFF;font-size:15px;font-weight:700;text-decoration:none;padding:14px 32px;border-radius:10px;"><?php esc_html_e( 'Review Entry', 'eethal-learning' ); ?> &rarr;</a>
			<div style="font-size:12.5px;color:#8A8DA8;margin-top:12px;"><?php echo esc_html( sprintf( /* translators: %s: date and time. */ __( 'Submitted on %s', 'eethal-learning' ), $submitted ) ); ?></div>
		</td></tr>

		<!-- Footer -->
		<tr><td style="padding:22px 36px 28px;">
			<div style="border-top:1px dashed #E6E8F2;padding-top:18px;font-size:12px;line-height:1.6;color:#A3A6BD;text-align:center;">
				<?php esc_html_e( 'Approved entries are added to the Working Professionals or Students list automatically.', 'eethal-learning' ); ?><br>
				<?php echo esc_html( sprintf( /* translators: %s: site name. */ __( 'You are receiving this because you are the admin of %s.', 'eethal-learning' ), $site_name ) ); ?>
			</div>
		</td></tr>

	</table>
</td></tr>
</table>
</body>
</html>
	<?php
	return ob_get_clean();
}
