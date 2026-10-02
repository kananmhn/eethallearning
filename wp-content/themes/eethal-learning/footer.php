<?php
/**
 * Site footer, CTA band and the shared modal markup.
 *
 * @package Eethal_Learning
 */

?>
</div><!-- #content -->

<?php
if ( eethal_section_enabled( 'cta' ) ) {
	get_template_part( 'template-parts/sections/cta' );
}
?>

<footer class="footer-bottom">
	<div class="container">
		<p>
			&copy; <span id="year"><?php echo esc_html( gmdate( 'Y' ) ); ?></span>
			<?php eethal_the_opt( 'footer_text' ); ?>
		</p>
	</div>
</footer>

<!-- Shared modal, populated by main.js when a mentor or alumni card is clicked -->
<div class="modal-overlay" id="commonModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="m-name">
	<div class="modal-content">
		<button class="modal-close" id="closeModal" aria-label="<?php esc_attr_e( 'Close', 'eethal-learning' ); ?>">&times;</button>
		<div class="modal-body">
			<div id="m-avatar" class="modal-avatar"></div>
			<div id="m-name" class="modal-name"></div>
			<div id="m-sub" class="modal-sub"></div>
			<div id="m-text" class="modal-text"></div>
			<div id="m-tags" class="modal-tags"></div>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>

</html>
