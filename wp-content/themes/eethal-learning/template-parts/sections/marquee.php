<?php
/**
 * Scrolling marquee.
 *
 * The list is printed twice so the CSS translation loops seamlessly.
 *
 * @package Eethal_Learning
 */

$items = eethal_marquee_items();

if ( empty( $items ) ) {
	return;
}
?>
<div class="marquee-wrap" aria-hidden="true">
	<div class="marquee-track" id="marquee-track">
		<?php for ( $pass = 0; $pass < 2; $pass++ ) : ?>
			<?php foreach ( $items as $item ) : ?>
				<span class="marquee-item"><span class="dot"></span><?php echo esc_html( $item ); ?></span>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>
