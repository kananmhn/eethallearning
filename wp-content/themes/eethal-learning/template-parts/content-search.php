<?php
/**
 * Result card used on the search page.
 *
 * @package Eethal_Learning
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'course-card post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="course-image-wrap" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'eethal-course', array( 'class' => 'course-image' ) ); ?>
		</a>
	<?php endif; ?>

	<div class="course-body">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<a href="<?php the_permalink(); ?>" class="course-btn"><?php esc_html_e( 'View', 'eethal-learning' ); ?></a>
	</div>
</article>
