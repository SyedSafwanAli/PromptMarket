<?php
/**
 * The site footer and document close.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

?>
	<footer class="site-footer" role="contentinfo">
		<div class="container site-footer__inner">

			<div class="site-footer__brand">
				<span class="site-footer__name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="site-footer__tagline"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'promptatlas' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'site-footer__list',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<p class="site-footer__legal">
				<?php
				printf(
					/* translators: %1$s: current year, %2$s: site name. */
					esc_html__( '© %1$s %2$s', 'promptatlas' ),
					esc_html( (string) gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>

		</div>
	</footer>

</div><!-- .site-wrap -->

<?php wp_footer(); ?>
</body>
</html>
