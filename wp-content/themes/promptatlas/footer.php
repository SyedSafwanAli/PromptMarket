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

			<?php
			if ( has_nav_menu( 'footer' ) ) :
				?>
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
				<?php
			else :
				/*
				 * Policy links, rendered only for pages that actually exist and
				 * are published. Phase 17 creates them; until then the footer
				 * simply omits the link rather than shipping a dead one.
				 *
				 * Once Phase 17 has run, assigning a Footer menu replaces this
				 * fallback entirely.
				 */
				$promptatlas_policy_pages = array(
					'about'          => __( 'About', 'promptatlas' ),
					'contact'        => __( 'Contact', 'promptatlas' ),
					'privacy-policy' => __( 'Privacy Policy', 'promptatlas' ),
					'terms'          => __( 'Terms', 'promptatlas' ),
					'dmca'           => __( 'DMCA', 'promptatlas' ),
					'accessibility'  => __( 'Accessibility', 'promptatlas' ),
				);

				$promptatlas_policy_links = array();

				foreach ( $promptatlas_policy_pages as $promptatlas_slug => $promptatlas_label ) {
					$promptatlas_url = promptatlas_page_url( $promptatlas_slug );

					if ( '' !== $promptatlas_url ) {
						$promptatlas_policy_links[ $promptatlas_url ] = $promptatlas_label;
					}
				}

				if ( ! empty( $promptatlas_policy_links ) ) :
					?>
					<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'promptatlas' ); ?>">
						<ul class="site-footer__list">
							<?php foreach ( $promptatlas_policy_links as $promptatlas_url => $promptatlas_label ) : ?>
								<li>
									<a href="<?php echo esc_url( $promptatlas_url ); ?>"><?php echo esc_html( $promptatlas_label ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
					<?php
				endif;
			endif;
			?>

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
