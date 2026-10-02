<?php
/**
 * FAQ accordion.
 *
 * @package Eethal_Learning
 */

$faqs = eethal_get_items( 'eethal_faq' );
?>
<section id="faq" class="section">
	<div class="container" style="max-width:800px">
		<div class="section-label reveal"><?php eethal_the_opt( 'faq_label' ); ?></div>
		<h2 class="section-title reveal"><?php eethal_the_opt( 'faq_title' ); ?></h2>

		<div class="faq-list reveal">
			<?php if ( ! empty( $faqs ) ) : ?>

				<?php foreach ( $faqs as $faq ) : ?>
					<div class="faq-item">
						<div class="faq-q" role="button" tabindex="0" aria-expanded="false">
							<?php echo esc_html( get_the_title( $faq ) ); ?>
							<div class="faq-icon" aria-hidden="true">+</div>
						</div>
						<div class="faq-a">
							<div class="faq-a-inner"><?php echo wp_kses_post( apply_filters( 'the_content', $faq->post_content ) ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>

			<?php else : ?>

				<?php foreach ( eethal_default_faqs() as $faq ) : ?>
					<div class="faq-item">
						<div class="faq-q" role="button" tabindex="0" aria-expanded="false">
							<?php echo esc_html( $faq['q'] ); ?>
							<div class="faq-icon" aria-hidden="true">+</div>
						</div>
						<div class="faq-a">
							<div class="faq-a-inner"><?php echo esc_html( $faq['a'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>
	</div>
</section>
