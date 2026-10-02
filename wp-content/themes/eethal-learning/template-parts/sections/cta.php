<?php
/**
 * Closing call-to-action band, shown above the footer on every page.
 *
 * @package Eethal_Learning
 */

$email    = eethal_opt( 'email' );
$phone    = eethal_opt( 'phone' );
$btn2_url = trim( (string) eethal_opt( 'cta_btn_2_url' ) );
if ( '' === $btn2_url && $email ) {
	$btn2_url = 'mailto:' . sanitize_email( $email );
}
?>
<div id="enroll" class="footer-top">
	<div class="container">
		<div class="footer-top-inner">
			<div>
				<h2 class="reveal"><?php eethal_the_opt( 'cta_title' ); ?></h2>
				<p class="reveal"><?php eethal_the_opt( 'cta_text' ); ?></p>
				<div class="hero-btns reveal">
					<?php if ( eethal_opt( 'cta_btn' ) ) : ?>
						<a href="<?php echo eethal_button_url( 'cta_btn_url' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"
							target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg">
							<?php eethal_the_opt( 'cta_btn' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( eethal_opt( 'cta_btn_2' ) && $btn2_url ) : ?>
						<a href="<?php echo esc_url( $btn2_url, array( 'http', 'https', 'mailto', 'tel' ) ); ?>" class="btn btn-outline-white btn-lg">
							<?php eethal_the_opt( 'cta_btn_2' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="nav-col">
				<div class="logo" style="margin-bottom:1.5rem">
					<span style="color:var(--white);font-weight:800;font-size:1.4rem"><?php eethal_the_opt( 'cta_brand' ); ?></span>
				</div>
				<?php if ( $phone ) : ?>
					<p style="font-size:0.85rem;color:rgba(255,255,255,0.6);margin-bottom:1rem">
						<?php echo esc_html( trim( eethal_opt( 'cta_phone_label' ) . ' ' . $phone ) ); ?>
					</p>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<p style="font-size:0.85rem;color:rgba(255,255,255,0.6)">
						<?php echo esc_html( trim( eethal_opt( 'cta_email_label' ) . ' ' . $email ) ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
