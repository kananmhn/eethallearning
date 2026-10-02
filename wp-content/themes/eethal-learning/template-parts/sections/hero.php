<?php
/**
 * Hero section.
 *
 * @package Eethal_Learning
 */

$hero_image_id = (int) get_theme_mod( 'eethal_hero_image', 0 );
?>
<section id="home" class="hero">
	<div class="hero-wrap container">
		<div class="hero-text">
			<?php if ( eethal_opt( 'hero_eyebrow' ) ) : ?>
				<div class="hero-eyebrow reveal"><?php eethal_the_opt( 'hero_eyebrow' ); ?></div>
			<?php endif; ?>

			<h1 class="hero-title reveal"><?php eethal_the_opt_nl2br( 'hero_title' ); ?></h1>

			<?php if ( eethal_opt( 'hero_desc' ) ) : ?>
				<p class="hero-desc reveal"><?php eethal_the_opt( 'hero_desc' ); ?></p>
			<?php endif; ?>

			<?php if ( eethal_opt( 'hero_btn' ) ) : ?>
				<div class="hero-btns reveal">
					<a href="<?php echo eethal_button_url( 'hero_btn_url' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>"
						class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
						<?php eethal_the_opt( 'hero_btn' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<div class="hero-img-wrap reveal">
			<?php
			if ( $hero_image_id ) {
				echo wp_get_attachment_image( $hero_image_id, 'full', false, array( 'class' => 'hero-img' ) );
			} else {
				printf(
					'<img src="%1$s" alt="%2$s" class="hero-img">',
					esc_url( eethal_img( 'assets/images/vtnf/hero-main.png' ) ),
					esc_attr__( 'Build Your IT Career', 'eethal-learning' )
				);
			}
			?>
		</div>
	</div>
</section>
