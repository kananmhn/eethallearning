<?php
/**
 * Search form.
 *
 * @package Eethal_Learning
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'eethal-learning' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'eethal-learning' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<button type="submit" class="search-submit btn btn-primary"><?php esc_html_e( 'Search', 'eethal-learning' ); ?></button>
</form>
