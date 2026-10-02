<?php
/**
 * Template for single posts.
 *
 * @package Eethal_Learning
 */

get_header();
?>

<main id="primary" class="site-main section">
	<div class="container container-narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
				<header class="page-header">
					<h1 class="section-title"><?php the_title(); ?></h1>
					<?php eethal_entry_meta(); ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry-thumbnail"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>

				<div class="entry-content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'eethal-learning' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>

				<footer class="entry-footer">
					<?php
					the_tags( '<div class="entry-tags">', '', '</div>' );
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-label">' . esc_html__( 'Previous', 'eethal-learning' ) . '</span> %title',
							'next_text' => '<span class="nav-label">' . esc_html__( 'Next', 'eethal-learning' ) . '</span> %title',
						)
					);
					?>
				</footer>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
