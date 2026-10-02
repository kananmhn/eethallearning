<?php
/**
 * Document head and site header.
 *
 * @package Eethal_Learning
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( eethal_opt( 'particles' ) ) : ?>
	<canvas id="particles-canvas" aria-hidden="true"></canvas>
<?php endif; ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'eethal-learning' ); ?></a>

<header id="masthead">
	<div class="header-inner">
		<div class="logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn" style="display:flex;align-items:center;position:relative">
					<img src="<?php echo esc_url( eethal_img( 'assets/images/eethal-white-logo.png' ) ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="logo-img logo-light">
					<img src="<?php echo esc_url( eethal_img( 'assets/images/eethal-white-logo.png' ) ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="logo-img logo-dark">
				</a>
			<?php endif; ?>
		</div>

		<button class="nav-toggle" id="nav-toggle" aria-controls="primary-menu" aria-expanded="false">
			<span class="nav-toggle-bar"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'eethal-learning' ); ?></span>
		</button>

		<nav id="site-navigation" aria-label="<?php esc_attr_e( 'Primary', 'eethal-learning' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
						'depth'          => 2,
					)
				);
			} else {
				eethal_default_menu();
			}
			?>
		</nav>

		<?php
		$eethal_wa = eethal_whatsapp_url();
		if ( $eethal_wa ) :
			?>
			<a href="<?php echo esc_url( $eethal_wa ); ?>" class="header-phone" target="_blank" rel="noopener noreferrer">
				<span class="wa-icon-wrap"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
				<span class="phone-number"><?php eethal_the_opt( 'phone' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
</header>

<div id="content" class="site-content">
