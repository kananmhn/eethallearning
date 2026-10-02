<?php
/**
 * Alumni testimonials carousel.
 *
 * @package Eethal_Learning
 */

$testimonials = eethal_get_items( 'eethal_testimonial' );
$palette      = array( 'av-r', 'av-i', 'av-g' );
?>
<section id="testimonials" class="section" style="background:var(--off-white)">
	<div class="container">
		<div class="section-label reveal"><?php eethal_the_opt( 'test_label' ); ?></div>
		<h2 class="section-title reveal"><?php eethal_the_opt( 'test_title' ); ?></h2>

		<div class="carousel-container reveal" style="margin-top:2rem">
			<div class="carousel-scroll-area">
				<div class="test-grid" id="test-carousel">
					<?php if ( ! empty( $testimonials ) ) : ?>

						<?php
						foreach ( $testimonials as $i => $testimonial ) :
							$name     = get_the_title( $testimonial );
							$initials = eethal_meta( $testimonial->ID, 'initials' );
							$colour   = eethal_meta( $testimonial->ID, 'colour' );
							$role     = eethal_meta( $testimonial->ID, 'role' );
							$rating   = (int) eethal_meta( $testimonial->ID, 'rating', 5 );

							if ( '' === $initials ) {
								$initials = eethal_initials( $name );
							}
							if ( '' === $colour ) {
								$colour = eethal_avatar_class( $i, $palette );
							}
							?>
							<div class="test-card reveal">
								<?php if ( $rating > 0 ) : ?>
									<div class="stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating out of five */ __( '%d out of 5 stars', 'eethal-learning' ), $rating ) ); ?>"><?php echo esc_html( str_repeat( '★', min( 5, $rating ) ) ); ?></div>
								<?php endif; ?>
								<p class="test-text"><?php echo esc_html( wp_strip_all_tags( $testimonial->post_content ) ); ?></p>
								<div class="test-author">
									<div class="author-avatar <?php echo esc_attr( $colour ); ?>"><?php echo esc_html( $initials ); ?></div>
									<div class="author-info">
										<h5><?php echo esc_html( $name ); ?></h5><span><?php echo esc_html( $role ); ?></span>
									</div>
								</div>
							</div>
						<?php endforeach; ?>

					<?php else : ?>

						<?php foreach ( eethal_default_testimonials() as $testimonial ) : ?>
							<div class="test-card reveal">
								<div class="stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'eethal-learning' ); ?>">★★★★★</div>
								<p class="test-text"><?php echo esc_html( $testimonial['quote'] ); ?></p>
								<div class="test-author">
									<div class="author-avatar <?php echo esc_attr( $testimonial['colour'] ); ?>"><?php echo esc_html( $testimonial['initials'] ); ?></div>
									<div class="author-info">
										<h5><?php echo esc_html( $testimonial['name'] ); ?></h5><span><?php echo esc_html( $testimonial['role'] ); ?></span>
									</div>
								</div>
							</div>
						<?php endforeach; ?>

					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
