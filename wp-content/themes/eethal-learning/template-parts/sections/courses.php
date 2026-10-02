<?php
/**
 * Courses grid.
 *
 * Reads the Course post type. Until the first course is published the packaged
 * defaults are rendered, so the theme looks complete straight after activation.
 *
 * @package Eethal_Learning
 */

$courses          = eethal_get_items( 'eethal_course' );
$default_btn_text = eethal_opt( 'course_btn_text' );
?>
<section id="courses" class="section">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem">
			<div class="section-label reveal"><?php eethal_the_opt( 'courses_label' ); ?></div>
			<h2 class="section-title reveal"><?php eethal_the_opt( 'courses_title' ); ?></h2>
		</div>

		<div class="courses-grid">
			<?php if ( ! empty( $courses ) ) : ?>

				<?php
				foreach ( $courses as $course ) :
					$link     = eethal_meta( $course->ID, 'link' );
					$btn_text = eethal_meta( $course->ID, 'btn_text', $default_btn_text );
					$subtitle = eethal_meta( $course->ID, 'subtitle' );

					if ( '' === $subtitle ) {
						$subtitle = get_the_excerpt( $course );
					}
					if ( '' === $link ) {
						$link = eethal_opt( 'enroll_url' );
					}
					?>
					<div class="course-card reveal">
						<div class="course-image-wrap">
							<?php
							if ( has_post_thumbnail( $course ) ) {
								echo get_the_post_thumbnail( $course, 'eethal-course', array( 'class' => 'course-image' ) );
							} else {
								printf(
									'<img src="%1$s" alt="%2$s" class="course-image">',
									esc_url( eethal_img( 'assets/images/course-frontend.png' ) ),
									esc_attr( get_the_title( $course ) )
								);
							}
							?>
						</div>
						<div class="course-body">
							<h3><?php echo esc_html( get_the_title( $course ) ); ?></h3>
							<?php if ( $subtitle ) : ?>
								<p><?php echo esc_html( $subtitle ); ?></p>
							<?php endif; ?>
							<?php if ( $btn_text ) : ?>
								<a href="<?php echo esc_url( $link ); ?>" class="course-btn" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $btn_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>

			<?php else : ?>

				<?php foreach ( eethal_default_courses() as $course ) : ?>
					<div class="course-card reveal">
						<div class="course-image-wrap">
							<img src="<?php echo esc_url( eethal_img( $course['image'] ) ); ?>"
								alt="<?php echo esc_attr( $course['title'] ); ?>" class="course-image">
						</div>
						<div class="course-body">
							<h3><?php echo esc_html( $course['title'] ); ?></h3>
							<p><?php echo esc_html( $course['desc'] ); ?></p>
							<a href="<?php echo eethal_enroll_url(); // phpcs:ignore WordPress.Security.EscapeOutput ?>"
								class="course-btn" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $default_btn_text ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>
	</div>
</section>
