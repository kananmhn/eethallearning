<?php
/**
 * Talent Directory: activity log behind the app's notification bell.
 *
 * Profile and entry changes are recorded in the eethal_td_activity option (newest first,
 * capped at EETHAL_TD_ACTIVITY_MAX). Visitors only see public events (new profiles);
 * directory admins see everything, with the admin wording where there is one.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EETHAL_TD_ACTIVITY_MAX', 50 );

/**
 * Record an event.
 *
 * @param string     $kind       e.g. profile_added, entry_new.
 * @param string     $text       Wording shown to everyone allowed to see it.
 * @param bool       $is_public  Whether visitors see it too.
 * @param array|null $link       What clicking it opens: {type: professional|student|entries, id}.
 * @param string     $admin_text Wording for admins, if different.
 */
function eethal_td_log( $kind, $text, $is_public = false, $link = null, $admin_text = '' ) {
	$log = eethal_td_get_activity();
	array_unshift(
		$log,
		array(
			'id'        => wp_generate_uuid4(),
			'kind'      => $kind,
			'text'      => $text,
			'adminText' => $admin_text,
			'public'    => (bool) $is_public,
			'link'      => $link,
			'time'      => gmdate( 'c' ),
		)
	);
	update_option( 'eethal_td_activity', array_slice( $log, 0, EETHAL_TD_ACTIVITY_MAX ), false );
}

/**
 * The log, newest first. The first time, it is filled from the newest profiles so the
 * bell isn't empty on a site that already has data.
 *
 * @return array
 */
function eethal_td_get_activity() {
	$log = get_option( 'eethal_td_activity', null );
	if ( is_array( $log ) ) {
		return $log;
	}

	$log = array();
	foreach ( array( 'professional', 'student' ) as $type ) {
		$posts = get_posts(
			array(
				'post_type'   => eethal_td_post_type( $type ),
				'post_status' => 'publish',
				'numberposts' => 5,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
		foreach ( $posts as $post ) {
			$log[] = array(
				'id'        => wp_generate_uuid4(),
				'kind'      => 'profile_added',
				'text'      => eethal_td_added_text( $type, html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ) ),
				'adminText' => '',
				'public'    => true,
				'link'      => array(
					'type' => $type,
					'id'   => $post->ID,
				),
				'time'      => get_post_time( 'c', true, $post ),
			);
		}
	}
	usort(
		$log,
		function ( $a, $b ) {
			return strcmp( $b['time'], $a['time'] );
		}
	);
	update_option( 'eethal_td_activity', $log, false );
	return $log;
}

/**
 * "New professional “Name” was added to the directory."
 *
 * @param string $type "professional" or "student".
 * @param string $name Person's name.
 * @return string
 */
function eethal_td_added_text( $type, $name ) {
	return 'student' === $type
		/* translators: %s: name. */
		? sprintf( __( 'New student “%s” was added to the directory.', 'eethal-learning' ), $name )
		/* translators: %s: name. */
		: sprintf( __( 'New professional “%s” was added to the directory.', 'eethal-learning' ), $name );
}

/**
 * The list name for a profile type.
 *
 * @param string $type "professional" or "student".
 * @return string
 */
function eethal_td_list_name( $type ) {
	return 'student' === $type ? __( 'Students', 'eethal-learning' ) : __( 'Working Professionals', 'eethal-learning' );
}

/**
 * Name of the signed-in admin, for "by …" in admin wording.
 *
 * @return string
 */
function eethal_td_actor() {
	$user = wp_get_current_user();
	return $user->exists() ? $user->display_name : __( 'an admin', 'eethal-learning' );
}

/**
 * Register GET /eethal/v1/notifications.
 */
function eethal_td_register_activity_routes() {
	register_rest_route(
		'eethal/v1',
		'/notifications',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => 'eethal_td_can_manage', // Admins only; viewers don't get notifications.
			'callback'            => 'eethal_td_rest_notifications',
		)
	);
}
add_action( 'rest_api_init', 'eethal_td_register_activity_routes' );

/**
 * GET notifications: the 20 newest events this user may see.
 *
 * @return WP_REST_Response
 */
function eethal_td_rest_notifications() {
	$is_admin = eethal_td_can_manage();
	$items    = array();
	foreach ( eethal_td_get_activity() as $event ) {
		if ( ! $is_admin && empty( $event['public'] ) ) {
			continue;
		}
		$link = $event['link'];
		// Visitors can't open entries.
		if ( ! $is_admin && is_array( $link ) && 'entries' === $link['type'] ) {
			$link = null;
		}
		$items[] = array(
			'id'   => $event['id'],
			'kind' => $event['kind'],
			'text' => $is_admin && '' !== $event['adminText'] ? $event['adminText'] : $event['text'],
			'link' => $link,
			'time' => $event['time'],
		);
		if ( count( $items ) >= 20 ) {
			break;
		}
	}
	return rest_ensure_response( $items );
}
