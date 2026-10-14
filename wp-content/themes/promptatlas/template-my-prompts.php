<?php
/**
 * Template Name: My prompts (saved and recently copied)
 *
 * Renders the visitor's own Saved and Recently Copied lists.
 *
 * Everything on this page comes from the visitor's own browser storage. The
 * server knows nothing about who they are and stores nothing for them; it only
 * answers "do these prompt IDs still exist" so that deleted prompts drop out of
 * the list cleanly.
 *
 * The page emits `noindex, nofollow`: its content is different for every
 * visitor and empty for a crawler, so it has no business in search results.
 * Phase 12 must not undo this.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep the per-visitor lists out of search engines.
 *
 * @return void
 */
function promptatlas_my_prompts_noindex() {
	echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}
add_action( 'wp_head', 'promptatlas_my_prompts_noindex', 1 );

get_header();

$promptatlas_endpoint = esc_url_raw( rest_url( 'promptatlas/v1/prompts' ) );
?>

<main id="primary" class="site-main container" role="main">

	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'My prompts', 'promptatlas' ); ?></h1>
		<p class="page-header__description">
			<?php esc_html_e( 'Prompts you saved, and ones you copied recently.', 'promptatlas' ); ?>
		</p>
	</header>

	<?php
	/*
	 * Shown when JavaScript is unavailable. The lists cannot work without it,
	 * so the page says so plainly instead of showing two permanently empty
	 * sections that look broken.
	 */
	?>
	<div class="js-required">
		<p class="notice-message notice-message--error">
			<?php esc_html_e( 'This page needs JavaScript, because your saved prompts are stored in your own browser rather than on the server.', 'promptatlas' ); ?>
		</p>
	</div>

	<div class="stored-lists">

		<section
			class="stored-list js-stored-list"
			data-list="saved"
			data-endpoint="<?php echo esc_url( $promptatlas_endpoint ); ?>"
			aria-labelledby="saved-heading"
		>
			<header class="section-header">
				<h2 id="saved-heading" class="section-header__title"><?php esc_html_e( 'Saved', 'promptatlas' ); ?></h2>
				<button type="button" class="button button--quiet js-list-clear" hidden>
					<?php esc_html_e( 'Clear saved', 'promptatlas' ); ?>
				</button>
			</header>

			<p class="stored-list__empty js-list-empty">
				<?php esc_html_e( 'Nothing saved yet. Use the Save button on any prompt to keep it here.', 'promptatlas' ); ?>
			</p>

			<ul class="stored-list__items js-list-items" hidden></ul>
		</section>

		<section
			class="stored-list js-stored-list"
			data-list="recent"
			data-endpoint="<?php echo esc_url( $promptatlas_endpoint ); ?>"
			aria-labelledby="recent-heading"
		>
			<header class="section-header">
				<h2 id="recent-heading" class="section-header__title"><?php esc_html_e( 'Recently copied', 'promptatlas' ); ?></h2>
				<button type="button" class="button button--quiet js-list-clear" hidden>
					<?php esc_html_e( 'Clear history', 'promptatlas' ); ?>
				</button>
			</header>

			<p class="stored-list__empty js-list-empty">
				<?php esc_html_e( 'Nothing here yet. Prompts you copy are added automatically.', 'promptatlas' ); ?>
			</p>

			<ul class="stored-list__items js-list-items" hidden></ul>
		</section>

	</div>

	<aside class="stored-lists__privacy">
		<h2 class="prompt-single__meta-label"><?php esc_html_e( 'Where this is stored', 'promptatlas' ); ?></h2>
		<p>
			<?php
			esc_html_e(
				'These lists live in your browser only. We store the prompt IDs and the time you copied or saved them — nothing else, and nothing that identifies you. They are never sent to us, they do not follow you to another device or browser, and clearing your browser data removes them.',
				'promptatlas'
			);
			?>
		</p>
	</aside>

</main>

<?php
get_footer();
