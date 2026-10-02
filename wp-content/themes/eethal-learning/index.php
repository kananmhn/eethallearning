<?php
/**
 * The main template — used for the blog index and as the ultimate fallback.
 *
 * @package Eethal_Learning
 */

get_header();
?>

<main id="primary" class="site-main section">
	<div class="container">

		<?php if ( is_home() && ! is_front_page() ) : ?>
			<header class="page-header">
				<div class="section-label"><?php esc_html_e( 'Blog', 'eethal-learning' ); ?></div>
				<h1 class="section-title"><?php single_post_title(); ?></h1>
			</header>
		<?php endif; ?>

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
