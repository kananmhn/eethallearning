<?php
/**
 * Student outcomes.
 *
 * @package Eethal_Learning
 */

$outcomes_image_id = (int) get_theme_mod( 'eethal_outcomes_image', 0 );
$checklist         = apply_filters( 'eethal_outcomes_checklist', eethal_lines( 'outcomes_checklist' ) );
?>
<section id="outcomes" class="section" style="background:var(--off-white2)">
	<div class="container">
		<div class="outcomes-grid">
			<div class="outcomes-image reveal">
				<?php
				$img_attr = array( 'style' => 'width:95%;border-radius:47px;padding:25px;box-shadow:0 20px 40px rgba(0,0,0,0.1)' );
				if ( $outcomes_image_id ) {
					echo wp_get_attachment_image( $outcomes_image_id, 'full', false, $img_attr );
				} else {
					printf(
						'<img src="%1$s" alt="%2$s" style="%3$s">',
						esc_url( eethal_img( 'assets/images/students-outcome.jpeg' ) ),
						esc_attr__( 'Student Training', 'eethal-learning' ),
						esc_attr( $img_attr['style'] )
					);
				}
				?>
			</div>

			<div class="outcomes-content">
				<div class="reveal">
					<div class="section-label"><?php eethal_the_opt( 'outcomes_label' ); ?></div>
					<h2 class="section-title"><?php eethal_the_opt( 'outcomes_title' ); ?></h2>
					<?php if ( eethal_opt( 'outcomes_text' ) ) : ?>
						<p class="section-sub outcomes-p"><?php eethal_the_opt( 'outcomes_text' ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $checklist ) ) : ?>
					<ul class="check-list reveal" style="margin-bottom:2rem">
						<?php foreach ( $checklist as $item ) : ?>
							<li class="check-item"><span class="check-icon" aria-hidden="true">&#10003;</span> <?php echo esc_html( $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="outcome-stats">
					<div class="stat-card reveal">
						<span class="stat-name"><?php eethal_the_opt( 'outcomes_stat_1_label' ); ?></span>
						<span class="stat-val counter" data-target="<?php echo esc_attr( eethal_opt( 'stat_students' ) ); ?>" data-suffix="<?php echo esc_attr( eethal_opt( 'stat_students_suffix' ) ); ?>">0<?php eethal_the_opt( 'stat_students_suffix' ); ?></span>
					</div>
					<div class="stat-card reveal">
						<span class="stat-name"><?php eethal_the_opt( 'outcomes_stat_2_label' ); ?></span>
						<span class="stat-val counter" data-target="<?php echo esc_attr( eethal_opt( 'stat_placement' ) ); ?>" data-suffix="<?php echo esc_attr( eethal_opt( 'stat_placement_suffix' ) ); ?>">0<?php eethal_the_opt( 'stat_placement_suffix' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
