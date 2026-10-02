<?php
/**
 * Template Name: Talent Directory
 *
 * Mounts the Talent Directory app (assets/talent-directory). The app draws its
 * own header, navigation and sign-in, so the site header and footer are not used.
 *
 * @package Eethal_Learning
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php esc_attr_e( 'Eethal Learning Talent Directory — Discover working professionals and students from Tamil Nadu.', 'eethal-learning' ); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'eethal-talent-directory' ); ?>>
<?php wp_body_open(); ?>

<!-- Skeleton shown until the app loads -->
<div id="root">
	<div style="display:flex;flex-direction:column;min-height:100vh;">
		<div class="skel-topbar">
			<div class="skel" style="width:140px;height:32px;border-radius:8px"></div>
			<div class="skel" style="width:300px;height:36px;border-radius:10px;margin:0 auto"></div>
			<div style="margin-left:auto;display:flex;gap:12px;align-items:center">
				<div class="skel" style="width:40px;height:40px;border-radius:10px"></div>
				<div class="skel" style="width:90px;height:40px;border-radius:10px"></div>
			</div>
		</div>
		<div class="skel-subnav">
			<div class="skel" style="width:90px;height:32px;border-radius:9px;background:rgba(255,255,255,0.1)"></div>
			<div class="skel" style="width:140px;height:32px;border-radius:9px;background:rgba(255,255,255,0.1)"></div>
			<div class="skel" style="width:90px;height:32px;border-radius:9px;background:rgba(255,255,255,0.1)"></div>
		</div>
		<div class="skel-content">
			<div class="skel-row">
				<div class="skel-card"><div class="skel" style="height:24px;width:60%;margin-bottom:12px"></div><div class="skel" style="height:36px;width:40%"></div></div>
				<div class="skel-card"><div class="skel" style="height:24px;width:60%;margin-bottom:12px"></div><div class="skel" style="height:36px;width:40%"></div></div>
			</div>
			<div class="skel-grid">
				<?php for ( $eethal_i = 0; $eethal_i < 4; $eethal_i++ ) : ?>
					<div class="skel-card"><div class="skel" style="width:46px;height:46px;border-radius:50%;margin-bottom:14px"></div><div class="skel" style="height:16px;width:80%;margin-bottom:8px"></div><div class="skel" style="height:13px;width:60%"></div></div>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>

</html>
