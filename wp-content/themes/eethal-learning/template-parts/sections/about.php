<?php
/**
 * About / mission section.
 *
 * @package Eethal_Learning
 */

$about_image_id = (int) get_theme_mod( 'eethal_about_image', 0 );
$checklist      = apply_filters( 'eethal_about_checklist', eethal_lines( 'about_checklist' ) );
?>
<section id="about" class="section">
	<div class="container">
		<div class="about-grid">
			<div class="about-content">
				<div class="section-label reveal"><?php eethal_the_opt( 'about_label' ); ?></div>
				<h2 class="section-title reveal"><?php eethal_the_opt_nl2br( 'about_title' ); ?></h2>

				<?php if ( eethal_opt( 'about_text_1' ) ) : ?>
					<p class="section-sub reveal"><?php eethal_the_opt( 'about_text_1' ); ?></p>
				<?php endif; ?>

				<?php if ( eethal_opt( 'about_text_2' ) ) : ?>
					<p class="section-sub reveal"><?php eethal_the_opt( 'about_text_2' ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $checklist ) ) : ?>
					<ul class="check-list reveal">
						<?php foreach ( $checklist as $item ) : ?>
							<li class="check-item"><span class="check-icon" aria-hidden="true">&#10003;</span> <?php echo esc_html( $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="about-stats reveal">
					<div class="astat">
						<span class="astat-num counter" data-target="<?php echo esc_attr( eethal_opt( 'stat_students' ) ); ?>" data-suffix="<?php echo esc_attr( eethal_opt( 'stat_students_suffix' ) ); ?>">0<?php eethal_the_opt( 'stat_students_suffix' ); ?></span>
						<span class="astat-label"><?php eethal_the_opt( 'stat_students_label' ); ?></span>
					</div>
					<div class="astat">
						<span class="astat-num counter" data-target="<?php echo esc_attr( eethal_opt( 'stat_placement' ) ); ?>" data-suffix="<?php echo esc_attr( eethal_opt( 'stat_placement_suffix' ) ); ?>">0<?php eethal_the_opt( 'stat_placement_suffix' ); ?></span>
						<span class="astat-label"><?php eethal_the_opt( 'stat_placement_label' ); ?></span>
					</div>
				</div>
			</div>

			<div class="about-image-wrap reveal">
				<?php
				if ( $about_image_id ) {
					echo wp_get_attachment_image( $about_image_id, 'full', false, array( 'class' => 'about-image' ) );
				} else {
					printf(
						'<img src="%1$s" alt="%2$s" class="about-image">',
						esc_url( eethal_img( 'assets/images/vtnf/training.png' ) ),
						esc_attr__( 'Our Training Environment', 'eethal-learning' )
					);
				}
				?>
			</div>
		</div>
	</div>
</section>
