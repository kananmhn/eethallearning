<?php
/**
 * Enroll Now settings page in wp-admin (Enroll Now in the admin menu).
 *
 * Edits the Enroll Now form's wording and the public forms' spam settings. The same
 * settings are also in Appearance → Customize → Eethal Front Page; both screens are
 * built from eethal_enroll_setting_groups(), so they always show the same fields and
 * save to the same theme settings (read with eethal_opt()).
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The editable settings, grouped. Field types: "text" or "textarea".
 *
 * @return array Group id => {title, desc, fields: id => {label, type, desc}}.
 */
function eethal_enroll_setting_groups() {
	$q_help = __( 'Label | hint shown inside the empty box', 'eethal-learning' );
	$field  = function ( $label, $type = 'text', $desc = '' ) {
		return array(
			'label' => $label,
			'type'  => $type,
			'desc'  => $desc,
		);
	};

	return array(
		'eethal_enroll' => array(
			'title'  => __( 'Enroll Now Form', 'eethal-learning' ),
			'desc'   => __( 'Text of the application form behind every "Enroll Now" button. Write {batch} anywhere to show the batch name. Questions are written as "Label | hint shown inside the empty box".', 'eethal-learning' ),
			'fields' => array(
				'enroll_batch'        => $field( __( 'Batch', 'eethal-learning' ), 'text', __( 'Saved with each application, so you can filter by batch. Change it when a new batch opens, e.g. Batch 9.', 'eethal-learning' ) ),
				'enroll_badge'        => $field( __( 'Badge above the heading', 'eethal-learning' ) ),
				'enroll_title'        => $field( __( 'Heading', 'eethal-learning' ) ),
				'enroll_intro'        => $field( __( 'Welcome text', 'eethal-learning' ), 'textarea', __( 'The white paragraph under the heading. Line breaks are kept.', 'eethal-learning' ) ),
				'enroll_note'         => $field( __( 'Smaller text under the welcome', 'eethal-learning' ), 'textarea' ),
				'enroll_section_1'    => $field( __( 'First group title', 'eethal-learning' ) ),
				'enroll_q_name'       => $field( __( 'Question: name', 'eethal-learning' ), 'text', $q_help ),
				'enroll_q_email'      => $field( __( 'Question: email', 'eethal-learning' ), 'text', $q_help ),
				'enroll_q_mobile'     => $field( __( 'Question: mobile number', 'eethal-learning' ), 'text', $q_help ),
				'enroll_q_dob'        => $field( __( 'Question: date of birth', 'eethal-learning' ) ),
				'enroll_q_district'   => $field( __( 'Question: district', 'eethal-learning' ), 'text', $q_help ),
				'enroll_q_referred'   => $field( __( 'Question: referred by', 'eethal-learning' ), 'text', $q_help ),
				'enroll_q_status'     => $field( __( 'Question: current status', 'eethal-learning' ) ),
				'enroll_statuses'     => $field( __( 'Current status choices', 'eethal-learning' ), 'textarea', __( 'One choice per line.', 'eethal-learning' ) ),
				'enroll_section_2'    => $field( __( 'Second group title', 'eethal-learning' ) ),
				'enroll_q_degree'     => $field( __( 'Question: degree', 'eethal-learning' ), 'text', __( 'Label | hint for the "Other" box', 'eethal-learning' ) ),
				'enroll_degrees'      => $field( __( 'Degree choices', 'eethal-learning' ), 'textarea', __( 'One choice per line.', 'eethal-learning' ) ),
				'enroll_degree_other' => $field( __( '"Other" degree choice', 'eethal-learning' ), 'text', __( 'Lets people type their own degree. Leave empty to remove it.', 'eethal-learning' ) ),
				'enroll_q_college'    => $field( __( 'Question: college name', 'eethal-learning' ), 'text', $q_help ),
				'enroll_q_passed'     => $field( __( 'Question: year passed out', 'eethal-learning' ), 'text', $q_help ),
				'enroll_button'       => $field( __( 'Submit button', 'eethal-learning' ) ),
				'enroll_thanks_title' => $field( __( 'Thank-you heading', 'eethal-learning' ), 'text', __( '{name} is replaced with the applicant\'s first name.', 'eethal-learning' ) ),
				'enroll_thanks_text'  => $field( __( 'Thank-you message', 'eethal-learning' ), 'textarea' ),
			),
		),
		'eethal_spam'   => array(
			'title'  => __( 'Form Spam Protection', 'eethal-learning' ),
			'desc'   => __( 'Applies to the Enroll Now form and the Entry Form. Both already block hidden-field bots, scripts that skip the page, links, repeat submissions and more than 5 sends an hour from one connection. For stronger protection, add a free Cloudflare Turnstile "I\'m not a robot" check: create a widget at dash.cloudflare.com → Turnstile for this site\'s domain and paste both keys below. Leave them empty to turn it off.', 'eethal-learning' ),
			'fields' => array(
				'spam_turnstile_site'       => $field( __( 'Turnstile site key', 'eethal-learning' ) ),
				'spam_turnstile_secret'     => $field( __( 'Turnstile secret key', 'eethal-learning' ), 'text', __( 'Kept on the server; never shown on the site.', 'eethal-learning' ) ),
				'spam_msg_expired'          => $field( __( 'Message: form expired', 'eethal-learning' ), 'textarea', __( 'When the page was open for over a day, or the form was sent from outside the site.', 'eethal-learning' ) ),
				'spam_msg_fast'             => $field( __( 'Message: sent too fast', 'eethal-learning' ), 'textarea', __( 'When the form is sent within seconds of the page opening.', 'eethal-learning' ) ),
				'spam_msg_links'            => $field( __( 'Message: links in answers', 'eethal-learning' ), 'textarea' ),
				'spam_msg_captcha'          => $field( __( 'Message: robot check not done', 'eethal-learning' ), 'textarea' ),
				'spam_msg_limit'            => $field( __( 'Message: too many submissions', 'eethal-learning' ), 'textarea' ),
				'spam_msg_duplicate_enroll' => $field( __( 'Message: already applied (Enroll Now)', 'eethal-learning' ), 'textarea', __( 'Same email or mobile in the same batch. {batch} shows the batch name.', 'eethal-learning' ) ),
				'spam_msg_duplicate_entry'  => $field( __( 'Message: already submitted (Entry Form)', 'eethal-learning' ), 'textarea', __( 'Same email or mobile as an entry still waiting for review.', 'eethal-learning' ) ),
			),
		),
	);
}

/**
 * Add Enroll Now to the admin menu.
 */
function eethal_enroll_admin_menu() {
	add_menu_page(
		__( 'Enroll Now Form', 'eethal-learning' ),
		__( 'Enroll Now', 'eethal-learning' ),
		'edit_theme_options',
		'eethal-enroll',
		'eethal_enroll_admin_page',
		'dashicons-welcome-learn-more',
		26
	);
}
add_action( 'admin_menu', 'eethal_enroll_admin_menu' );

/**
 * Save the settings page.
 */
function eethal_enroll_admin_save() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to change these settings.', 'eethal-learning' ), 403 );
	}
	check_admin_referer( 'eethal_enroll_settings' );

	$defaults = eethal_defaults();
	$posted   = isset( $_POST['eethal'] ) && is_array( $_POST['eethal'] ) ? wp_unslash( $_POST['eethal'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
	foreach ( eethal_enroll_setting_groups() as $group ) {
		foreach ( $group['fields'] as $id => $field ) {
			if ( ! isset( $posted[ $id ] ) ) {
				continue;
			}
			$value = 'textarea' === $field['type'] ? sanitize_textarea_field( $posted[ $id ] ) : sanitize_text_field( $posted[ $id ] );
			// Unchanged defaults aren't stored, so later changes to inc/defaults.php still apply.
			if ( isset( $defaults[ $id ] ) && str_replace( "\r\n", "\n", $value ) === $defaults[ $id ] ) {
				remove_theme_mod( 'eethal_' . $id );
			} else {
				set_theme_mod( 'eethal_' . $id, $value );
			}
		}
	}

	wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=eethal-enroll' ) ) );
	exit;
}
add_action( 'admin_post_eethal_enroll_settings', 'eethal_enroll_admin_save' );

/**
 * Render the settings page.
 */
function eethal_enroll_admin_page() {
	$urls = eethal_td_page_urls();
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Enroll Now Settings', 'eethal-learning' ); ?></h1>
		<a href="<?php echo esc_url( $urls['enroll'] ); ?>" class="page-title-action" target="_blank"><?php esc_html_e( 'View form', 'eethal-learning' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( 'view', 'enrollments', $urls['dashboard'] ) ); ?>" class="page-title-action" target="_blank"><?php esc_html_e( 'View applications', 'eethal-learning' ); ?></a>
		<hr class="wp-header-end">

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'eethal-learning' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="eethal_enroll_settings">
			<?php wp_nonce_field( 'eethal_enroll_settings' ); ?>

			<?php foreach ( eethal_enroll_setting_groups() as $group ) : ?>
				<h2 class="title"><?php echo esc_html( $group['title'] ); ?></h2>
				<p class="description" style="max-width:780px;"><?php echo esc_html( $group['desc'] ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					foreach ( $group['fields'] as $id => $field ) :
						$name  = 'eethal[' . $id . ']';
						$value = (string) eethal_opt( $id );
						?>
						<tr>
							<th scope="row"><label for="eethal-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php if ( 'textarea' === $field['type'] ) : ?>
									<textarea id="eethal-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
								<?php else : ?>
									<input type="text" id="eethal-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" style="width:100%;max-width:640px;">
								<?php endif; ?>
								<?php if ( $field['desc'] ) : ?>
									<p class="description"><?php echo esc_html( $field['desc'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>

			<?php submit_button( __( 'Save Changes', 'eethal-learning' ) ); ?>
		</form>
	</div>
	<?php
}
