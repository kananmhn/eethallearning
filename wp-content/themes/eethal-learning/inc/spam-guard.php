<?php
/**
 * Spam protection for the public forms (Enroll Now and the Entry Form).
 *
 * Every submission goes through eethal_spam_guard() before it is saved:
 *
 * 1. Honeypot: a hidden "website" box people never see. Bots that fill it get a fake
 *    "thank you" and nothing is saved.
 * 2. Form token: the page carries a signed timestamp (EETHAL_TD.spam.token). Requests
 *    without a valid one — bots posting straight to the API — are refused, and so are
 *    forms sent back within a few seconds of the page loading, which only a script does.
 * 3. No links: web addresses in the answers are refused (spam is almost always links).
 * 4. Optional "I'm not a robot" check (Cloudflare Turnstile, free) once its keys are
 *    entered in Customize → Eethal Front Page → Form Spam Protection.
 * 5. At most 5 submissions per hour from one address.
 *
 * Each form also refuses repeats of a submission that is already waiting (see
 * inc/enrollments.php and inc/talent-entries.php). The messages people see are edited
 * in the same Customizer section. The browser side is assets/talent-directory/src/spam.jsx.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// A form sent back sooner than this after the page loaded is treated as a script.
define( 'EETHAL_SPAM_MIN_SECONDS', 4 );
// A form token stays valid this long; after that the page has to be reloaded.
define( 'EETHAL_SPAM_MAX_AGE', DAY_IN_SECONDS );
// Submissions allowed per form, per address, per hour.
define( 'EETHAL_SPAM_HOURLY_LIMIT', 5 );

/**
 * A signed "this page was loaded at" token for the public forms.
 *
 * @param int|null $time Timestamp; now when omitted.
 * @return string "time.signature"
 */
function eethal_spam_token( $time = null ) {
	$time = null === $time ? time() : (int) $time;
	return $time . '.' . substr( hash_hmac( 'sha256', 'eethal-form|' . $time, wp_salt( 'nonce' ) ), 0, 24 );
}

/**
 * The spam settings the app needs (EETHAL_TD.spam).
 *
 * @return array
 */
function eethal_spam_config() {
	return array(
		'token'        => eethal_spam_token(),
		'turnstileKey' => trim( (string) eethal_opt( 'spam_turnstile_site' ) ),
	);
}

/**
 * A message from Customizer → Form Spam Protection, falling back to the default if emptied.
 *
 * @param string $key Setting key.
 * @return string
 */
function eethal_spam_msg( $key ) {
	$text = trim( (string) eethal_opt( $key ) );
	return '' !== $text ? $text : eethal_defaults()[ $key ];
}

/**
 * The visitor's IP address.
 *
 * @return string
 */
function eethal_spam_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

/**
 * Whether the visitor passed the Turnstile check. Always true while no keys are set.
 *
 * @param string $answer The widget's response token.
 * @return bool
 */
function eethal_spam_captcha_ok( $answer ) {
	$secret = trim( (string) eethal_opt( 'spam_turnstile_secret' ) );
	if ( '' === $secret || '' === trim( (string) eethal_opt( 'spam_turnstile_site' ) ) ) {
		return true;
	}
	if ( '' === $answer ) {
		return false;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => $secret,
				'response' => $answer,
				'remoteip' => eethal_spam_ip(),
			),
		)
	);
	// If Cloudflare can't be reached, let the person through; the other checks still apply.
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return true;
	}
	$result = json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $result['success'] );
}

/**
 * Check a public form submission for spam before it is validated and saved.
 *
 * @param array  $body The JSON body.
 * @param string $form Form id, e.g. "enroll" or "entry" (keeps the hourly limits apart).
 * @return true|WP_Error|WP_REST_Response True to go on; otherwise return this from the
 *                                        endpoint (a fake success for obvious bots).
 */
function eethal_spam_guard( array $body, $form ) {
	// Bots fill the hidden "website" box; pretend it worked so they don't retry.
	if ( ! empty( $body['website'] ) ) {
		return rest_ensure_response( array( 'success' => true ) );
	}

	$error = function ( $code, $key, $status = 400 ) {
		return new WP_Error( 'eethal_spam_' . $code, eethal_spam_msg( $key ), array( 'status' => $status ) );
	};

	// The token proves the form was loaded from the site, and when.
	$token = (string) ( $body['formToken'] ?? '' );
	$time  = (int) strtok( $token, '.' );
	if ( ! $time || ! hash_equals( eethal_spam_token( $time ), $token ) || time() - $time > EETHAL_SPAM_MAX_AGE ) {
		return $error( 'expired', 'spam_msg_expired' );
	}
	if ( time() - $time < EETHAL_SPAM_MIN_SECONDS ) {
		return $error( 'too_fast', 'spam_msg_fast' );
	}

	if ( count( eethal_spam_limit( $form ) ) >= EETHAL_SPAM_HOURLY_LIMIT ) {
		return $error( 'too_many', 'spam_msg_limit', 429 );
	}

	// Real answers never need a web address; spam almost always has one.
	// "photo" may be an image or Google Drive link, so it is left out.
	foreach ( $body as $key => $value ) {
		if ( is_string( $value ) && ! in_array( $key, array( 'photo', 'formToken', 'captcha', 'website' ), true )
			&& preg_match( '~(https?://|www\.|<a[\s>]|\[url)~i', $value ) ) {
			return $error( 'links', 'spam_msg_links' );
		}
	}

	if ( ! eethal_spam_captcha_ok( (string) ( $body['captcha'] ?? '' ) ) ) {
		return $error( 'captcha', 'spam_msg_captcha' );
	}
	return true;
}

/**
 * This hour's submissions of a form from the visitor's address.
 *
 * @param string $form Form id.
 * @return int[] Timestamps.
 */
function eethal_spam_limit( $form ) {
	$times = get_transient( 'eethal_spam_' . $form . '_' . md5( eethal_spam_ip() ) );
	$since = time() - HOUR_IN_SECONDS;
	return array_values(
		array_filter(
			is_array( $times ) ? $times : array(),
			function ( $t ) use ( $since ) {
				return $t > $since;
			}
		)
	);
}

/**
 * Count a saved submission towards the visitor's hourly limit.
 *
 * @param string $form Form id.
 */
function eethal_spam_count( $form ) {
	$times   = eethal_spam_limit( $form );
	$times[] = time();
	set_transient( 'eethal_spam_' . $form . '_' . md5( eethal_spam_ip() ), $times, HOUR_IN_SECONDS );
}
