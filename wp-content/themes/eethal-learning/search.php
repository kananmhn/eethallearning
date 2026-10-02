<?php
/**
 * Search results.
 *
 * @package Eethal_Learning
 */

get_header();
?>

<main id="primary" class="site-main section">
	<div class="container">
		<header class="page-header">
			<h1 class="section-title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Results for: %s', 'eethal-learning' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'search' );
				endwhile;
				?>
			</div>

			<?php eethal_pagination(); ?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
