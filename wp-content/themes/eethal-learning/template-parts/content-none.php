<?php
/**
 * Shown when a query returns no results.
 *
 * @package Eethal_Learning
 */

?>
<div class="no-results" style="text-align:center;padding:3rem 0">
	<h2 class="section-title"><?php esc_html_e( 'Nothing found', 'eethal-learning' ); ?></h2>
	<p class="section-sub" style="margin-left:auto;margin-right:auto">
		<?php esc_html_e( 'Try a different search, or browse our courses.', 'eethal-learning' ); ?>
	</p>
	<div style="max-width:420px;margin:2rem auto"><?php get_search_form(); ?></div>
</div>
