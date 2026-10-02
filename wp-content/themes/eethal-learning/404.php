<?php
/**
 * 404 template.
 *
 * @package Eethal_Learning
 */

get_header();
?>

<main id="primary" class="site-main section">
	<div class="container container-narrow" style="text-align:center">
		<div class="section-label"><?php esc_html_e( 'Error 404', 'eethal-learning' ); ?></div>
		<h1 class="section-title"><?php esc_html_e( 'That page has moved on.', 'eethal-learning' ); ?></h1>
		<p class="section-sub" style="margin-left:auto;margin-right:auto">
			<?php esc_html_e( 'The page you were looking for is not here. Try a search, or head back to the home page.', 'eethal-learning' ); ?>
		</p>

		<div style="max-width:420px;margin:2rem auto"><?php get_search_form(); ?></div>

		<div class="hero-btns" style="justify-content:center">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-lg">
				<?php esc_html_e( 'Back to Home', 'eethal-learning' ); ?>
			</a>
		</div>
	</div>
</main>

<?php
get_footer();
