<?php
/**
 * Community photo section.
 *
 * The original markup reused the `why-choose-us` id here; this uses a unique
 * `community` id so in-page anchors resolve correctly.
 *
 * @package Eethal_Learning
 */

if ( ! eethal_opt( 'bonding_enable' ) ) {
	return;
}

$bonding_image_id = (int) get_theme_mod( 'eethal_bonding_image', 0 );
?>
<section id="community" class="section">
	<div class="container">
		<div style="text-align:center">
			<div class="section-label reveal"><?php eethal_the_opt( 'bonding_label' ); ?></div>
			<h2 class="section-title reveal"><?php eethal_the_opt( 'bonding_title' ); ?></h2>
		</div>
		<div class="grpimg reveal">
			<?php
			if ( $bonding_image_id ) {
				echo wp_get_attachment_image( $bonding_image_id, 'full' );
			} else {
				printf(
					'<img src="%1$s" alt="%2$s">',
					esc_url( eethal_img( 'assets/images/vtnf/bonding-image.jpeg' ) ),
					esc_attr__( 'Eethal Learning community', 'eethal-learning' )
				);
			}
			?>
		</div>
	</div>
</section>
