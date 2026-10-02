<?php
/**
 * Why choose us / advantages.
 *
 * @package Eethal_Learning
 */

$advantages = apply_filters( 'eethal_advantages', eethal_icon_items( 'why_items' ) );

if ( empty( $advantages ) ) {
	return;
}
?>
<section id="why-choose-us" class="section">
	<div class="container">
		<div style="text-align:center">
			<div class="section-label reveal"><?php eethal_the_opt( 'why_label' ); ?></div>
			<h2 class="section-title reveal"><?php eethal_the_opt( 'why_title' ); ?></h2>
		</div>
		<div class="why-grid">
			<?php foreach ( $advantages as $advantage ) : ?>
				<div class="why-item reveal">
					<?php if ( '' !== $advantage[0] ) : ?>
						<div class="why-icon-box" aria-hidden="true"><?php echo esc_html( $advantage[0] ); ?></div>
					<?php endif; ?>
					<h4><?php echo esc_html( $advantage[1] ); ?></h4>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
