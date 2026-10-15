<?php
/**
 * Who receives the theme's admin notification emails.
 *
 * @package Eethal_Learning
 */

defined( 'ABSPATH' ) || exit;

/**
 * Email addresses of the site admin plus every user with the Administrator role.
 *
 * @return string[] Unique, valid addresses.
 */
function eethal_admin_recipients() {
	$emails = array( get_option( 'admin_email' ) );

	foreach ( get_users( array( 'role' => 'administrator', 'fields' => array( 'user_email' ) ) ) as $user ) {
		$emails[] = $user->user_email;
	}

	return array_values( array_unique( array_filter( array_map( 'sanitize_email', $emails ), 'is_email' ) ) );
}

add_filter( 'eethal_enroll_notify_to', 'eethal_admin_recipients' );
add_filter( 'eethal_td_entry_notify_to', 'eethal_admin_recipients' );
