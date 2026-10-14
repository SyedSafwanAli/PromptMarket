<?php
/**
 * The fallback template.
 *
 * WordPress uses this when no more specific template matches. It must stay
 * functional rather than being an empty placeholder, because any content type
 * added later lands here first.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main container" role="main">

	<?php if ( have_posts() ) : ?>

		<header class="page-header">
			<h1 class="page-title"><?php echo esc_html( promptatlas_current_page_label() ); ?></h1>
		</header>

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
			__( 'Nothing here yet', 'promptatlas' ),
			__( 'There is no content to show on this page at the moment.', 'promptatlas' )
		);
		?>

	<?php endif; ?>

</main>

<?php
get_footer();
