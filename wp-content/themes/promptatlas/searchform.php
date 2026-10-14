<?php
/**
 * The search form.
 *
 * A unique id is generated per instance because the form appears more than once
 * on some pages (the header and the 404 page), and duplicate ids would break
 * the label-to-input association for assistive technology.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

$promptatlas_search_id = wp_unique_id( 'promptatlas-search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $promptatlas_search_id ); ?>">
		<?php esc_html_e( 'Search prompts', 'promptatlas' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $promptatlas_search_id ); ?>"
		class="search-form__input"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search prompts…', 'promptatlas' ); ?>"
	>
	<button type="submit" class="search-form__submit">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'promptatlas' ); ?></span>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
			<circle cx="11" cy="11" r="7"/>
			<path d="m20 20-3.5-3.5"/>
		</svg>
	</button>
</form>
