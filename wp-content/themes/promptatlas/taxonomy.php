<?php
/**
 * Archive for the prompt taxonomies: category, tool and tag.
 *
 * One template for all three rather than three near-identical files. The only
 * genuine difference is the framing sentence above the list, which is chosen by
 * taxonomy below. Duplicating the layout three times to vary one sentence would
 * be three places to update for every future change.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$promptatlas_term  = get_queried_object();
$promptatlas_tax   = $promptatlas_term instanceof WP_Term ? $promptatlas_term->taxonomy : '';
$promptatlas_count = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;

/*
 * The framing line. Written per taxonomy because "prompts tagged resume" and
 * "prompts written for ChatGPT" mean different things to a reader arriving from
 * search, and a single generic sentence would serve neither.
 */
$promptatlas_intro = '';

if ( $promptatlas_term instanceof WP_Term ) {
	switch ( $promptatlas_tax ) {
		case 'prompt_tool':
			$promptatlas_intro = sprintf(
				/* translators: %s: the AI tool name. */
				__( 'Prompts written for %s. Most work in other assistants too — the explanation on each page says where it matters.', 'promptatlas' ),
				$promptatlas_term->name
			);
			break;

		case 'prompt_tag':
			$promptatlas_intro = sprintf(
				/* translators: %s: the tag name. */
				__( 'Prompts tagged %s, across every category.', 'promptatlas' ),
				$promptatlas_term->name
			);
			break;

		case 'prompt_category':
		default:
			$promptatlas_intro = sprintf(
				/* translators: %s: the category name. */
				__( 'Prompts for %s. Each one is explained, so you know what to change before you use it.', 'promptatlas' ),
				$promptatlas_term->name
			);
			break;
	}
}

/*
 * A term description written by an editor always beats the generated sentence
 * above, so it wins when present.
 */
$promptatlas_description = term_description();
?>

<main id="primary" class="site-main container" role="main">

	<?php promptatlas_breadcrumbs(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php echo esc_html( promptatlas_current_page_label() ); ?></h1>

		<?php if ( $promptatlas_description ) : ?>
			<div class="page-header__description prose">
				<?php echo wp_kses_post( $promptatlas_description ); ?>
			</div>
		<?php elseif ( '' !== $promptatlas_intro ) : ?>
			<p class="page-header__description"><?php echo esc_html( $promptatlas_intro ); ?></p>
		<?php endif; ?>

		<?php if ( $promptatlas_count > 0 ) : ?>
			<p class="page-header__meta">
				<?php
				printf(
					/* translators: %s: number of prompts. */
					esc_html( _n( '%s prompt', '%s prompts', $promptatlas_count, 'promptatlas' ) ),
					esc_html( number_format_i18n( $promptatlas_count ) )
				);
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', 'prompt' );
			endwhile;
			?>
		</div>

		<?php promptatlas_pagination(); ?>

	<?php else : ?>

		<div class="empty-state">
			<h2 class="empty-state__heading"><?php esc_html_e( 'Nothing here yet', 'promptatlas' ); ?></h2>
			<p class="empty-state__message">
				<?php esc_html_e( 'No published prompts carry this term so far.', 'promptatlas' ); ?>
			</p>
			<p>
				<a class="button button--secondary" href="<?php echo esc_url( (string) get_post_type_archive_link( 'prompt' ) ); ?>">
					<?php esc_html_e( 'Browse all prompts', 'promptatlas' ); ?>
				</a>
			</p>
		</div>

	<?php endif; ?>

</main>

<?php
get_footer();
