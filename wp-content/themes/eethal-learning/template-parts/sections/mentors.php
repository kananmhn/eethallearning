<?php
/**
 * Mentors carousel.
 *
 * @package Eethal_Learning
 */

$mentors = eethal_get_items( 'eethal_mentor' );
$palette = array( 'ia1', 'ia2', 'ia3' );
?>
<section id="mentors" class="section">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem">
			<div class="section-label reveal"><?php eethal_the_opt( 'mentors_label' ); ?></div>
			<h2 class="section-title reveal"><?php eethal_the_opt( 'mentors_title' ); ?></h2>
			<?php if ( eethal_opt( 'mentors_sub' ) ) : ?>
				<p class="section-sub reveal" style="margin-left:auto;margin-right:auto"><?php eethal_the_opt( 'mentors_sub' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="carousel-container reveal">
			<div class="carousel-scroll-area">
				<div class="inst-grid" id="inst-carousel">
					<?php if ( ! empty( $mentors ) ) : ?>

						<?php
						foreach ( $mentors as $i => $mentor ) :
							$name     = get_the_title( $mentor );
							$initials = eethal_meta( $mentor->ID, 'initials' );
							$colour   = eethal_meta( $mentor->ID, 'colour' );
							$role     = eethal_meta( $mentor->ID, 'role' );
							$tags     = array_filter( array_map( 'trim', explode( ',', (string) eethal_meta( $mentor->ID, 'tags' ) ) ) );

							if ( '' === $initials ) {
								$initials = eethal_initials( $name );
							}
							if ( '' === $colour ) {
								$colour = eethal_avatar_class( $i, $palette );
							}
							?>
							<div class="inst-card reveal">
								<div class="inst-avatar <?php echo esc_attr( $colour ); ?>"><?php echo esc_html( $initials ); ?></div>
								<div class="inst-name"><?php echo esc_html( $name ); ?></div>
								<div class="inst-role"><?php echo esc_html( $role ); ?></div>
								<div class="inst-exp"><?php echo esc_html( wp_strip_all_tags( $mentor->post_content ) ); ?></div>
								<?php if ( $tags ) : ?>
									<div class="inst-tags">
										<?php foreach ( $tags as $tag ) : ?>
											<span class="tag"><?php echo esc_html( $tag ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>

					<?php else : ?>

						<?php foreach ( eethal_default_mentors() as $mentor ) : ?>
							<div class="inst-card reveal">
								<div class="inst-avatar <?php echo esc_attr( $mentor['colour'] ); ?>"><?php echo esc_html( $mentor['initials'] ); ?></div>
								<div class="inst-name"><?php echo esc_html( $mentor['name'] ); ?></div>
								<div class="inst-role"><?php echo esc_html( $mentor['role'] ); ?></div>
								<div class="inst-exp"><?php echo esc_html( $mentor['bio'] ); ?></div>
								<div class="inst-tags">
									<?php foreach ( $mentor['tags'] as $tag ) : ?>
										<span class="tag"><?php echo esc_html( $tag ); ?></span>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endforeach; ?>

					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
