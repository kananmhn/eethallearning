<?php
/**
 * Benefits bar.
 *
 * @package Eethal_Learning
 */

$benefits = apply_filters( 'eethal_benefits', eethal_icon_items( 'benefits_items' ) );

if ( empty( $benefits ) ) {
	return;
}
?>
<div class="benefits-bar">
	<?php foreach ( $benefits as $benefit ) : ?>
		<div class="benefit-item reveal">
			<?php if ( '' !== $benefit[0] ) : ?>
				<div class="benefit-icon" aria-hidden="true"><?php echo esc_html( $benefit[0] ); ?></div>
			<?php endif; ?>
			<div class="benefit-text"><?php echo esc_html( $benefit[1] ); ?></div>
		</div>
	<?php endforeach; ?>
</div>
