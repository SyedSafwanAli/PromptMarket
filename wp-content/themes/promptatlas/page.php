<?php
/**
 * Template for static pages.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main container" role="main">

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>

			<?php promptatlas_breadcrumbs(); ?>

			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-media">
					<?php the_post_thumbnail( 'promptatlas-hero', array( 'loading' => 'eager' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="entry-content prose">
				<?php the_content(); ?>
			</div>

		</article>
		<?php
	endwhile;
	?>

</main>

<?php
get_footer();
