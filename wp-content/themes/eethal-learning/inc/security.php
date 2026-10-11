<?php
/**
 * Site hardening: security headers, a smaller attack surface and login protection.
 *
 * The public forms are guarded separately (inc/spam-guard.php).
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Failed logins allowed per address before a temporary lockout.
define( 'EETHAL_LOGIN_MAX_FAILS', 5 );
// How long the lockout (and the failure count) lasts.
define( 'EETHAL_LOGIN_LOCK_SECONDS', 15 * MINUTE_IN_SECONDS );

/**
 * Browser-side protections sent with every page.
 *
 * No Content-Security-Policy on purpose: the page loads fonts, icons and the optional
 * Turnstile widget from third parties, and a wrong policy would silently break them.
 *
 * @param array $headers Headers WordPress is about to send.
 * @return array
 */
function eethal_security_headers( $headers ) {
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['Permissions-Policy']     = 'camera=(), microphone=(), geolocation=(), payment=()';
	if ( is_ssl() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
	}
	return $headers;
}
add_filter( 'wp_headers', 'eethal_security_headers' );

// Nobody edits theme/plugin code from wp-admin, so a stolen admin login can't either.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// XML-RPC is a common brute-force and DDoS route and nothing here uses it.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Don't advertise the WordPress version.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Stop the "?author=1" redirect that reveals usernames.
 */
function eethal_block_author_enumeration() {
	if ( ! is_admin() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'eethal_block_author_enumeration', 1 );

/**
 * Hide the user list from the public REST API (it exposes login names).
 *
 * @param array $endpoints REST routes.
 * @return array
 */
function eethal_restrict_rest_users( $endpoints ) {
	if ( ! current_user_can( 'list_users' ) ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'eethal_restrict_rest_users' );

/**
 * Same message for a wrong username and a wrong password.
 *
 * @return string
 */
function eethal_generic_login_error() {
	return esc_html__( 'Incorrect username or password.', 'eethal-learning' );
}
add_filter( 'login_errors', 'eethal_generic_login_error' );

/**
 * Transient key for an address's failed-login count.
 *
 * @return string
 */
function eethal_login_key() {
	return 'eethal_login_' . md5( eethal_spam_ip() );
}

/**
 * Refuse logins from an address that has failed too many times recently.
 *
 * @param WP_User|WP_Error|null $user Result so far.
 * @return WP_User|WP_Error|null
 */
function eethal_login_lockout( $user ) {
	if ( (int) get_transient( eethal_login_key() ) >= EETHAL_LOGIN_MAX_FAILS ) {
		return new WP_Error(
			'eethal_locked',
			esc_html__( 'Too many failed attempts. Please try again in a few minutes.', 'eethal-learning' )
		);
	}
	return $user;
}
add_filter( 'authenticate', 'eethal_login_lockout', 30 );

/**
 * Count a failed login.
 */
function eethal_login_failed() {
	set_transient( eethal_login_key(), (int) get_transient( eethal_login_key() ) + 1, EETHAL_LOGIN_LOCK_SECONDS );
}
add_action( 'wp_login_failed', 'eethal_login_failed' );

/**
 * Forget the failures after a good login.
 */
function eethal_login_succeeded() {
	delete_transient( eethal_login_key() );
}
add_action( 'wp_login', 'eethal_login_succeeded' );
