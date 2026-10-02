<?php
/**
 * Post card used in the blog index and archives.
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
		<?php if ( 'post' === get_post_type() ) { eethal_entry_meta(); } ?>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<a href="<?php the_permalink(); ?>" class="course-btn"><?php esc_html_e( 'Read More', 'eethal-learning' ); ?></a>
	</div>
</article>
