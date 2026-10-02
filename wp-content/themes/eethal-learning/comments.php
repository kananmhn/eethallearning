<?php
/**
 * Comments template.
 *
 * @package Eethal_Learning
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$eethal_count = get_comments_number();
			printf(
				/* translators: %s: comment count */
				esc_html( _n( '%s Comment', '%s Comments', $eethal_count, 'eethal-learning' ) ),
				esc_html( number_format_i18n( $eethal_count ) )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation();

		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'eethal-learning' ); ?></p>
			<?php
		endif;
	endif;

	comment_form();
	?>
</div>
