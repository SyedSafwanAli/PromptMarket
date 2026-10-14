<?php
/**
 * Template for archives: post type archives and taxonomy terms.
 *
 * Phase 08 adds the richer archive-prompt.php and taxonomy templates. This is
 * the working general case and the fallback.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main container" role="main">

	<?php promptatlas_breadcrumbs(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php echo esc_html( promptatlas_current_page_label() ); ?></h1>
		<?php
		$promptatlas_description = term_description();

		if ( $promptatlas_description ) :
			?>
			<div class="page-header__description prose">
				<?php echo wp_kses_post( $promptatlas_description ); ?>
			</div>
			<?php
		endif;
		?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', get_post_type() );
			endwhile;
			?>
		</div>

		<?php promptatlas_pagination(); ?>

	<?php else : ?>

		<?php
		promptatlas_empty_state(
			__( 'No prompts here yet', 'promptatlas' ),
			__( 'Nothing has been published in this section so far. New prompts are added regularly.', 'promptatlas' )
		);
		?>

	<?php endif; ?>

</main>

<?php
get_footer();
