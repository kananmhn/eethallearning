<?php
/**
 * Talent Directory: Working Professionals and Students.
 *
 * Profiles are stored as two private post types. The React directory app is
 * mounted on the Dashboard, Working Professionals and Students pages (which use
 * template-talent-directory.php) and talks to the REST routes under
 * /wp-json/eethal/v1/. Only users with the manage_talent_directory capability
 * (administrators and the Talent Directory Admin role) can sign in to the app
 * and add, edit or delete profiles.
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EETHAL_TD_VERSION', '1' );
define( 'EETHAL_TD_TEMPLATE', 'template-talent-directory.php' );
define( 'EETHAL_TD_CAP', 'manage_talent_directory' );

/**
 * The app's three top-level views and the pages that host them.
 *
 * @return array
 */
function eethal_td_views() {
	return array(
		'dashboard'     => array(
			'slug'  => 'dashboard',
			'title' => __( 'Dashboard', 'eethal-learning' ),
		),
		'professionals' => array(
			'slug'  => 'working-professionals',
			'title' => __( 'Working Professionals', 'eethal-learning' ),
		),
		'students'      => array(
			'slug'  => 'students',
			'title' => __( 'Students', 'eethal-learning' ),
		),
	);
}

/**
 * Post type for a profile type.
 *
 * @param string $type "professional" or "student".
 * @return string
 */
function eethal_td_post_type( $type ) {
	return 'professional' === $type ? 'eethal_professional' : 'eethal_student';
}

/**
 * Profile fields (besides the name, which is the post title), as named in the app.
 *
 * @param string $type "professional" or "student".
 * @return string[]
 */
function eethal_td_fields( $type ) {
	$common = array( 'mobile', 'email', 'district', 'education', 'address', 'photo', 'marital' );
	if ( 'professional' === $type ) {
		return array_merge( $common, array( 'experience', 'company', 'designation', 'ctc', 'verified' ) );
	}
	return array_merge( $common, array( 'specialization', 'gradYear' ) );
}

/**
 * Post meta key for an app field, e.g. gradYear => _eethal_grad_year.
 *
 * @param string $field App field name.
 * @return string
 */
function eethal_td_meta_key( $field ) {
	return '_eethal_' . strtolower( preg_replace( '/([A-Z])/', '_$1', $field ) );
}

/**
 * Register the profile post types.
 */
function eethal_td_register_post_types() {
	register_post_type(
		'eethal_professional',
		array(
			'labels'        => array(
				'name'          => esc_html__( 'Working Professionals', 'eethal-learning' ),
				'singular_name' => esc_html__( 'Working Professional', 'eethal-learning' ),
				'add_new_item'  => esc_html__( 'Add New Professional', 'eethal-learning' ),
				'edit_item'     => esc_html__( 'Edit Professional', 'eethal-learning' ),
				'not_found'     => esc_html__( 'No professionals yet.', 'eethal-learning' ),
				'menu_name'     => esc_html__( 'Professionals', 'eethal-learning' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-id-alt',
			'menu_position' => 24,
			'supports'      => array( 'title' ),
		)
	);

	register_post_type(
		'eethal_student',
		array(
			'labels'        => array(
				'name'          => esc_html__( 'Students', 'eethal-learning' ),
				'singular_name' => esc_html__( 'Student', 'eethal-learning' ),
				'add_new_item'  => esc_html__( 'Add New Student', 'eethal-learning' ),
				'edit_item'     => esc_html__( 'Edit Student', 'eethal-learning' ),
				'not_found'     => esc_html__( 'No students yet.', 'eethal-learning' ),
				'menu_name'     => esc_html__( 'Students', 'eethal-learning' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 25,
			'supports'      => array( 'title' ),
		)
	);
}
add_action( 'init', 'eethal_td_register_post_types' );

/**
 * Add the directory admin role and capability (once per module version).
 */
function eethal_td_setup_roles() {
	if ( EETHAL_TD_VERSION === get_option( 'eethal_td_roles' ) ) {
		return;
	}

	remove_role( 'talent_admin' );
	add_role(
		'talent_admin',
		__( 'Talent Directory Admin', 'eethal-learning' ),
		array(
			'read'       => true,
			EETHAL_TD_CAP => true,
		)
	);

	$administrator = get_role( 'administrator' );
	if ( $administrator ) {
		$administrator->add_cap( EETHAL_TD_CAP );
	}

	update_option( 'eethal_td_roles', EETHAL_TD_VERSION );
}
add_action( 'init', 'eethal_td_setup_roles' );

/**
 * Create the three directory pages, unless a page with that slug already exists.
 */
function eethal_td_create_pages() {
	if ( get_option( 'eethal_td_pages_created' ) ) {
		return;
	}

	foreach ( eethal_td_views() as $view ) {
		if ( get_page_by_path( $view['slug'] ) ) {
			continue;
		}
		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => $view['title'],
				'post_name'   => $view['slug'],
			),
			true
		);
		if ( ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', EETHAL_TD_TEMPLATE );
		}
	}

	update_option( 'eethal_td_pages_created', EETHAL_TD_VERSION );
}
add_action( 'after_switch_theme', 'eethal_td_create_pages' );

/**
 * Create the pages for a theme that was already active before this file existed.
 */
function eethal_td_maybe_create_pages() {
	if ( wp_doing_ajax() || get_option( 'eethal_td_pages_created' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	eethal_td_create_pages();
}
add_action( 'admin_init', 'eethal_td_maybe_create_pages' );

/**
 * Whether the current request is one of the directory app pages.
 *
 * @return bool
 */
function eethal_td_is_app_page() {
	return is_page_template( EETHAL_TD_TEMPLATE );
}

/**
 * The app view for the current page, based on its slug.
 *
 * @return string
 */
function eethal_td_current_view() {
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	foreach ( eethal_td_views() as $key => $view ) {
		if ( $view['slug'] === $slug ) {
			return $key;
		}
	}
	return 'dashboard';
}

/**
 * Permalinks of the three directory pages, keyed by view.
 *
 * @return array
 */
function eethal_td_page_urls() {
	$urls = array();
	foreach ( eethal_td_views() as $key => $view ) {
		$page         = get_page_by_path( $view['slug'] );
		$urls[ $key ] = $page ? get_permalink( $page ) : home_url( '/' . $view['slug'] . '/' );
	}
	return $urls;
}

/**
 * The app has its own header, so hide the admin bar on its pages.
 *
 * @param bool $show Whether to show the admin bar.
 * @return bool
 */
function eethal_td_admin_bar( $show ) {
	return eethal_td_is_app_page() ? false : $show;
}
add_filter( 'show_admin_bar', 'eethal_td_admin_bar' );

/**
 * Swap the landing-page assets for the directory app's assets.
 */
function eethal_td_enqueue() {
	if ( ! eethal_td_is_app_page() ) {
		return;
	}

	foreach ( array( 'eethal-style', 'eethal-fonts', 'font-awesome', 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
	wp_dequeue_script( 'eethal-main' );

	$dir = EETHAL_DIR . '/assets/talent-directory';
	$uri = EETHAL_URI . '/assets/talent-directory';

	wp_enqueue_style(
		'eethal-td-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'eethal-td', $uri . '/talent-directory.css', array( 'eethal-td-fonts' ), filemtime( $dir . '/talent-directory.css' ) );
	wp_enqueue_script( 'eethal-td-app', $uri . '/app.js', array( 'react', 'react-dom' ), filemtime( $dir . '/app.js' ), true );

	$user   = wp_get_current_user();
	$config = array(
		'restUrl' => esc_url_raw( rest_url( 'eethal/v1/' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'view'    => eethal_td_current_view(),
		'pages'   => eethal_td_page_urls(),
		'homeUrl' => home_url( '/' ),
		'user'    => current_user_can( EETHAL_TD_CAP )
			? array(
				'name'  => $user->display_name,
				'email' => $user->user_email,
			)
			: null,
	);
	wp_add_inline_script( 'eethal-td-app', 'window.EETHAL_TD = ' . wp_json_encode( $config ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'eethal_td_enqueue', 100 );

/* ------------------------------------------------------------------------- *
 * REST API
 * ------------------------------------------------------------------------- */

/**
 * Register the directory routes.
 */
function eethal_td_register_routes() {
	$namespace = 'eethal/v1';

	foreach ( array(
		'professionals' => 'professional',
		'students'      => 'student',
	) as $route => $type ) {
		register_rest_route(
			$namespace,
			'/' . $route,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => '__return_true',
					'callback'            => function () use ( $type ) {
						return eethal_td_rest_list( $type );
					},
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => 'eethal_td_can_manage',
					'callback'            => function ( WP_REST_Request $request ) use ( $type ) {
						return eethal_td_rest_save( $type, $request );
					},
				),
			)
		);

		register_rest_route(
			$namespace,
			'/' . $route . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'permission_callback' => 'eethal_td_can_manage',
					'callback'            => function ( WP_REST_Request $request ) use ( $type ) {
						return eethal_td_rest_save( $type, $request, (int) $request['id'] );
					},
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'permission_callback' => 'eethal_td_can_manage',
					'callback'            => function ( WP_REST_Request $request ) use ( $type ) {
						return eethal_td_rest_delete( $type, (int) $request['id'] );
					},
				),
			)
		);
	}

	register_rest_route(
		$namespace,
		'/auth/login',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => '__return_true',
			'callback'            => 'eethal_td_rest_login',
		)
	);

	register_rest_route(
		$namespace,
		'/auth/logout',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => 'is_user_logged_in',
			'callback'            => 'eethal_td_rest_logout',
		)
	);
}
add_action( 'rest_api_init', 'eethal_td_register_routes' );

/**
 * Whether the current user may add, edit and delete profiles.
 *
 * @return bool
 */
function eethal_td_can_manage() {
	return current_user_can( EETHAL_TD_CAP );
}

/**
 * A profile post in the shape the app expects.
 *
 * @param WP_Post $post Profile post.
 * @param string  $type "professional" or "student".
 * @return array
 */
function eethal_td_record( $post, $type ) {
	$record = array(
		'id'   => $post->ID,
		'name' => html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ),
	);
	foreach ( eethal_td_fields( $type ) as $field ) {
		$value            = get_post_meta( $post->ID, eethal_td_meta_key( $field ), true );
		$record[ $field ] = 'verified' === $field ? '1' === $value : (string) $value;
	}
	$record['createdAt'] = get_post_time( 'c', true, $post );
	return $record;
}

/**
 * GET: all published profiles of a type, newest first.
 *
 * @param string $type "professional" or "student".
 * @return WP_REST_Response
 */
function eethal_td_rest_list( $type ) {
	$posts = get_posts(
		array(
			'post_type'   => eethal_td_post_type( $type ),
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);

	$records = array();
	foreach ( $posts as $post ) {
		$records[] = eethal_td_record( $post, $type );
	}
	return rest_ensure_response( $records );
}

/**
 * POST / PUT: create a profile, or update the fields sent for an existing one.
 *
 * @param string          $type    "professional" or "student".
 * @param WP_REST_Request $request Request.
 * @param int             $post_id Profile to update; 0 to create.
 * @return WP_REST_Response|WP_Error
 */
function eethal_td_rest_save( $type, WP_REST_Request $request, $post_id = 0 ) {
	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) {
		return new WP_Error( 'eethal_td_invalid_json', __( 'Invalid JSON body.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	$post_type = eethal_td_post_type( $type );

	if ( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== $post_type ) {
			return new WP_Error( 'eethal_td_not_found', __( 'Profile not found.', 'eethal-learning' ), array( 'status' => 404 ) );
		}
	} elseif ( empty( $body['name'] ) || empty( $body['email'] ) || empty( $body['mobile'] ) ) {
		return new WP_Error( 'eethal_td_missing_fields', __( 'Name, email, and mobile are required.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	// Store an uploaded photo before touching the profile, so a bad image fails cleanly.
	$meta = array();
	foreach ( eethal_td_fields( $type ) as $field ) {
		if ( ! array_key_exists( $field, $body ) ) {
			continue;
		}
		$value = eethal_td_sanitize_field( $field, $body[ $field ] );
		if ( is_wp_error( $value ) ) {
			return $value;
		}
		if ( in_array( $field, eethal_td_filter_fields(), true ) ) {
			$value = eethal_td_existing_spelling( $field, eethal_td_tidy_filter_value( $field, $value ), $post_id );
		}
		$meta[ $field ] = $value;
	}
	if ( ! $post_id && 'professional' === $type ) {
		$meta['verified'] = '0';
	}

	$postarr = array(
		'post_type'   => $post_type,
		'post_status' => 'publish',
	);
	if ( $post_id ) {
		$postarr['ID'] = $post_id;
	}
	if ( isset( $body['name'] ) ) {
		$postarr['post_title'] = sanitize_text_field( $body['name'] );
	}

	$result = $post_id ? wp_update_post( $postarr, true ) : wp_insert_post( $postarr, true );
	if ( is_wp_error( $result ) ) {
		return new WP_Error( 'eethal_td_save_failed', $result->get_error_message(), array( 'status' => 500 ) );
	}

	foreach ( $meta as $field => $value ) {
		update_post_meta( $result, eethal_td_meta_key( $field ), $value );
	}

	$response = rest_ensure_response( eethal_td_record( get_post( $result ), $type ) );
	$response->set_status( $post_id ? 200 : 201 );
	return $response;
}

/**
 * Sanitize one incoming profile field.
 *
 * @param string $field App field name.
 * @param mixed  $value Raw value.
 * @return string|WP_Error
 */
function eethal_td_sanitize_field( $field, $value ) {
	switch ( $field ) {
		case 'verified':
			return rest_sanitize_boolean( $value ) ? '1' : '0';
		case 'address':
			return sanitize_textarea_field( (string) $value );
		case 'marital':
			$value = ucfirst( strtolower( trim( (string) $value ) ) );
			return in_array( $value, array( 'Married', 'Unmarried' ), true ) ? $value : '';
		case 'photo':
			return eethal_td_photo_value( (string) $value );
		default:
			// Also trims and collapses repeated spaces.
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Fields offered as filter options in the app.
 *
 * @return string[]
 */
function eethal_td_filter_fields() {
	return array( 'district', 'education', 'specialization', 'designation', 'company', 'gradYear' );
}

/**
 * Comparison key that ignores spaces, dots and case, so "B.E", "B. E", "BE" and "b.e"
 * match, as do "BE CSE" and "BE. CSE". "Bachelor of Engineering" keeps its own key.
 *
 * @param string $value Field value.
 * @return string
 */
function eethal_td_compact_key( $value ) {
	return strtolower( preg_replace( '/[\s.]+/u', '', (string) $value ) );
}

/**
 * Education is saved without a space after dots: "B. Tech" => "B.Tech".
 * Other fields keep dots as typed ("Sr. Associate").
 *
 * @param string $field App field name (or meta box key).
 * @param string $value Sanitized value.
 * @return string
 */
function eethal_td_tidy_filter_value( $field, $value ) {
	return 'education' === $field ? preg_replace( '/\.\s+/u', '.', (string) $value ) : $value;
}

/**
 * When a value differs from one already saved only by spaces, dots or case, reuse the saved
 * spelling (the most used one), so "BE" or "B. E" is stored as "B.E" if "B.E" exists.
 *
 * @param string $field      App field name.
 * @param string $value      Sanitized value.
 * @param int    $exclude_id Profile being saved, so its own old value doesn't count.
 * @return string
 */
function eethal_td_existing_spelling( $field, $value, $exclude_id = 0 ) {
	global $wpdb;

	$key = eethal_td_compact_key( $value );
	if ( '' === $key ) {
		return $value;
	}

	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT BINARY meta_value AS spelling, COUNT(*) AS uses FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id <> %d GROUP BY BINARY meta_value ORDER BY uses DESC, CHAR_LENGTH(meta_value) ASC",
			eethal_td_meta_key( $field ),
			$exclude_id
		)
	);
	foreach ( $rows as $row ) {
		if ( eethal_td_compact_key( $row->spelling ) === $key ) {
			return $row->spelling;
		}
	}
	return $value;
}

/**
 * One-time tidy of existing profiles: trims spaces in every field, removes the space after
 * dots in education, then merges filter values that differ only by spaces, dots or case
 * into their most used spelling.
 */
function eethal_td_tidy_existing_profiles() {
	if ( '4' === get_option( 'eethal_td_data_version' ) ) {
		return;
	}

	foreach ( array( 'professional', 'student' ) as $type ) {
		$ids = get_posts(
			array(
				'post_type'   => eethal_td_post_type( $type ),
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		foreach ( $ids as $id ) {
			foreach ( eethal_td_fields( $type ) as $field ) {
				if ( in_array( $field, array( 'photo', 'verified' ), true ) ) {
					continue;
				}
				$key = eethal_td_meta_key( $field );
				$old = get_post_meta( $id, $key, true );
				if ( ! is_string( $old ) || '' === $old ) {
					continue;
				}
				$new = eethal_td_sanitize_field( $field, $old );
				if ( is_string( $new ) ) {
					$new = eethal_td_tidy_filter_value( $field, $new );
				}
				if ( is_string( $new ) && $new !== $old ) {
					update_post_meta( $id, $key, $new );
				}
			}
		}
	}

	global $wpdb;
	foreach ( eethal_td_filter_fields() as $field ) {
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT BINARY meta_value AS spelling, COUNT(*) AS uses FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> '' GROUP BY BINARY meta_value ORDER BY uses DESC, CHAR_LENGTH(meta_value) ASC",
				eethal_td_meta_key( $field )
			)
		);
		$preferred = array();
		foreach ( $rows as $row ) {
			$key = eethal_td_compact_key( $row->spelling );
			if ( ! isset( $preferred[ $key ] ) ) {
				$preferred[ $key ] = $row->spelling; // Most used spelling wins.
				continue;
			}
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = %s AND BINARY meta_value = BINARY %s",
					$preferred[ $key ],
					eethal_td_meta_key( $field ),
					$row->spelling
				)
			);
		}
	}
	wp_cache_flush();

	update_option( 'eethal_td_data_version', '4' );
}
add_action( 'init', 'eethal_td_tidy_existing_profiles', 20 );

/**
 * Photo field: keep links as URLs, save uploaded images (data: URLs) to the Media Library.
 *
 * @param string $photo Image URL, Google Drive link or data: URL.
 * @return string|WP_Error URL to store.
 */
function eethal_td_photo_value( $photo ) {
	$photo = trim( $photo );
	if ( 0 !== strpos( $photo, 'data:' ) ) {
		return esc_url_raw( $photo );
	}

	$types = array(
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/gif'  => 'gif',
		'image/webp' => 'webp',
	);
	if ( ! preg_match( '#^data:(image/[a-z]+);base64,(.+)$#s', $photo, $matches ) || ! isset( $types[ $matches[1] ] ) ) {
		return new WP_Error( 'eethal_td_invalid_photo', __( 'Photo must be a JPEG, PNG, GIF or WebP image.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	$bytes = base64_decode( $matches[2], true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( false === $bytes || strlen( $bytes ) > 5 * MB_IN_BYTES ) {
		return new WP_Error( 'eethal_td_invalid_photo', __( 'Please select an image smaller than 5MB.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	$upload = wp_upload_bits( 'talent-photo-' . wp_generate_password( 8, false ) . '.' . $types[ $matches[1] ], null, $bytes );
	if ( ! empty( $upload['error'] ) ) {
		return new WP_Error( 'eethal_td_upload_failed', $upload['error'], array( 'status' => 500 ) );
	}
	if ( ! wp_getimagesize( $upload['file'] ) ) {
		wp_delete_file( $upload['file'] );
		return new WP_Error( 'eethal_td_invalid_photo', __( 'Photo must be a JPEG, PNG, GIF or WebP image.', 'eethal-learning' ), array( 'status' => 400 ) );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $matches[1],
			'post_title'     => __( 'Talent Directory photo', 'eethal-learning' ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $upload['url'];
}

/**
 * DELETE: remove a profile.
 *
 * @param string $type    "professional" or "student".
 * @param int    $post_id Profile ID.
 * @return array|WP_Error
 */
function eethal_td_rest_delete( $type, $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || eethal_td_post_type( $type ) !== $post->post_type ) {
		return new WP_Error( 'eethal_td_not_found', __( 'Profile not found.', 'eethal-learning' ), array( 'status' => 404 ) );
	}
	wp_delete_post( $post_id, true );
	return array( 'success' => true );
}

/**
 * POST auth/login: sign in with a WordPress email (or username) and password.
 *
 * Returns a fresh REST nonce, since the page's nonce belongs to the signed-out visitor.
 *
 * @param WP_REST_Request $request Request with email and password.
 * @return array|WP_Error
 */
function eethal_td_rest_login( WP_REST_Request $request ) {
	$login    = trim( (string) $request->get_param( 'email' ) );
	$password = (string) $request->get_param( 'password' );
	$user     = wp_authenticate( $login, $password );

	// A sign-in name can be an alias for another account (user meta eethal_td_login_alias),
	// e.g. "admin" for a directory-only account when "admin" is also the site administrator.
	if ( is_wp_error( $user ) && '' !== $login ) {
		$aliased = get_users(
			array(
				'meta_key'   => 'eethal_td_login_alias', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value' => strtolower( $login ), // phpcs:ignore WordPress.DB.SlowDBQuery
				'number'     => 1,
			)
		);
		if ( $aliased ) {
			$user = wp_authenticate( $aliased[0]->user_login, $password );
		}
	}

	if ( is_wp_error( $user ) ) {
		return new WP_Error( 'eethal_td_login_failed', __( 'Incorrect email or password.', 'eethal-learning' ), array( 'status' => 401 ) );
	}
	if ( ! user_can( $user, EETHAL_TD_CAP ) ) {
		return new WP_Error( 'eethal_td_not_admin', __( 'This account does not have admin access.', 'eethal-learning' ), array( 'status' => 403 ) );
	}

	// The nonce is tied to the session token in the logged-in cookie, which this request doesn't have yet.
	add_action( 'set_logged_in_cookie', 'eethal_td_capture_logged_in_cookie' );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	wp_set_current_user( $user->ID );
	do_action( 'wp_login', $user->user_login, $user ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	return array(
		'name'  => $user->display_name,
		'email' => $user->user_email,
		'nonce' => wp_create_nonce( 'wp_rest' ),
	);
}

/**
 * Expose the new logged-in cookie to the rest of the current request.
 *
 * @param string $cookie Logged-in cookie value.
 */
function eethal_td_capture_logged_in_cookie( $cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
}

/**
 * POST auth/logout: sign out and return a nonce for the signed-out visitor.
 *
 * @return array
 */
function eethal_td_rest_logout() {
	wp_logout();
	unset( $_COOKIE[ LOGGED_IN_COOKIE ] );
	return array( 'nonce' => wp_create_nonce( 'wp_rest' ) );
}
