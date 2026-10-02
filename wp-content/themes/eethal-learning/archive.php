<?php
/**
 * Template for archives, including the Courses archive.
 *
 * @package Eethal_Learning
 */

get_header();
?>

<main id="primary" class="site-main section">
	<div class="container">
		<header class="page-header">
			<?php
			the_archive_title( '<h1 class="section-title">', '</h1>' );
			the_archive_description( '<div class="section-sub">', '</div>' );
			?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
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
