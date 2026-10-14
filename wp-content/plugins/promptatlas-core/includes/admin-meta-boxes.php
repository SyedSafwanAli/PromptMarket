<?php
/**
 * Admin editing panels for prompts and collections.
 *
 * Classic meta boxes rather than block-editor sidebar panels: a block-editor
 * panel requires a JavaScript build step (@wordpress/scripts), and Phase 03
 * deliberately did not adopt a bundler. Meta boxes render in the block editor's
 * compatibility area with no build tooling and no JavaScript of our own.
 *
 * Security contract for every save handler in this file, in order:
 *   1. Ignore autosaves and revisions.
 *   2. Verify the nonce.
 *   3. Check `current_user_can( 'edit_post', $post_id )`.
 *   4. Only then read $_POST, unslash it, and pass it through the field's
 *      registered sanitiser.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nonce action for the prompt details panel.
 */
const PROMPTATLAS_NONCE_PROMPT = 'promptatlas_save_prompt_meta';

/**
 * Nonce field name for the prompt details panel.
 */
const PROMPTATLAS_NONCE_PROMPT_FIELD = 'promptatlas_prompt_nonce';

/**
 * Nonce action for the collection panel.
 */
const PROMPTATLAS_NONCE_COLLECTION = 'promptatlas_save_collection_meta';

/**
 * Nonce field name for the collection panel.
 */
const PROMPTATLAS_NONCE_COLLECTION_FIELD = 'promptatlas_collection_nonce';

/**
 * Register the meta boxes.
 *
 * @return void
 */
function promptatlas_add_meta_boxes() {
	add_meta_box(
		'promptatlas-prompt-text',
		__( 'Prompt text', 'promptatlas' ),
		'promptatlas_render_prompt_text_box',
		'prompt',
		'normal',
		'high'
	);

	add_meta_box(
		'promptatlas-prompt-variables',
		__( 'Placeholder variables', 'promptatlas' ),
		'promptatlas_render_variables_box',
		'prompt',
		'normal',
		'default'
	);

	add_meta_box(
		'promptatlas-prompt-example',
		__( 'Example output', 'promptatlas' ),
		'promptatlas_render_example_box',
		'prompt',
		'normal',
		'default'
	);

	add_meta_box(
		'promptatlas-prompt-sourcing',
		__( 'Sourcing and related prompts', 'promptatlas' ),
		'promptatlas_render_sourcing_box',
		'prompt',
		'side',
		'default'
	);

	add_meta_box(
		'promptatlas-collection-prompts',
		__( 'Prompts in this collection', 'promptatlas' ),
		'promptatlas_render_collection_box',
		'collection',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'promptatlas_add_meta_boxes' );

/**
 * Render the prompt text panel.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function promptatlas_render_prompt_text_box( $post ) {
	wp_nonce_field( PROMPTATLAS_NONCE_PROMPT, PROMPTATLAS_NONCE_PROMPT_FIELD );

	$text       = promptatlas_get_prompt_text( $post->ID );
	$mismatches = promptatlas_get_variable_mismatches( $post->ID );
	$undeclared = $mismatches['undeclared'];

	?>
	<p class="description">
		<?php
		esc_html_e(
			'The exact text a visitor copies. Mark anything they must replace with a placeholder in square brackets and capitals, for example [JOB_TITLE], then describe it in the Placeholder variables panel below.',
			'promptatlas'
		);
		?>
	</p>

	<textarea
		id="promptatlas_prompt_text"
		name="promptatlas_prompt_text"
		rows="14"
		class="large-text code"
		style="font-family: Consolas, Monaco, monospace; line-height: 1.6;"
		placeholder="<?php esc_attr_e( 'Paste or write the prompt here…', 'promptatlas' ); ?>"
	><?php echo esc_textarea( $text ); ?></textarea>

	<?php if ( ! empty( $undeclared ) ) : ?>
		<div class="notice notice-warning inline" style="margin: 12px 0 0;">
			<p>
				<strong><?php esc_html_e( 'Undescribed placeholders:', 'promptatlas' ); ?></strong>
				<?php echo esc_html( '[' . implode( '], [', $undeclared ) . ']' ); ?>
				<br>
				<?php esc_html_e( 'These appear in the prompt but have no description below. Visitors will not know what to put there. You can still save.', 'promptatlas' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $mismatches['unused'] ) ) : ?>
		<div class="notice notice-info inline" style="margin: 12px 0 0;">
			<p>
				<strong><?php esc_html_e( 'Described but not used:', 'promptatlas' ); ?></strong>
				<?php echo esc_html( implode( ', ', $mismatches['unused'] ) ); ?>
				<br>
				<?php esc_html_e( 'These variables are described below but do not appear in the prompt text.', 'promptatlas' ); ?>
			</p>
		</div>
	<?php endif; ?>
	<?php
}

/**
 * Render the placeholder variables panel.
 *
 * Six fixed rows rather than a JavaScript repeater: no build step, no custom
 * JavaScript, and six covers every realistic prompt. Empty rows are discarded
 * on save by promptatlas_sanitize_variables().
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function promptatlas_render_variables_box( $post ) {
	$variables = promptatlas_get_variables( $post->ID );
	$rows      = max( 6, count( $variables ) + 2 );

	?>
	<p class="description">
		<?php esc_html_e( 'Describe each placeholder from the prompt text. The name is normalised to capitals and underscores automatically. Leave unused rows empty.', 'promptatlas' ); ?>
	</p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th scope="col" style="width: 30%;"><?php esc_html_e( 'Placeholder name', 'promptatlas' ); ?></th>
				<th scope="col"><?php esc_html_e( 'What the visitor should put there', 'promptatlas' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php for ( $i = 0; $i < $rows; $i++ ) : ?>
				<?php
				$name        = isset( $variables[ $i ]['name'] ) ? $variables[ $i ]['name'] : '';
				$description = isset( $variables[ $i ]['description'] ) ? $variables[ $i ]['description'] : '';
				?>
				<tr>
					<td>
						<label class="screen-reader-text" for="promptatlas_variables_name_<?php echo esc_attr( (string) $i ); ?>">
							<?php esc_html_e( 'Placeholder name', 'promptatlas' ); ?>
						</label>
						<input
							type="text"
							id="promptatlas_variables_name_<?php echo esc_attr( (string) $i ); ?>"
							name="promptatlas_variables[<?php echo esc_attr( (string) $i ); ?>][name]"
							value="<?php echo esc_attr( $name ); ?>"
							class="regular-text code"
							placeholder="JOB_TITLE"
						>
					</td>
					<td>
						<label class="screen-reader-text" for="promptatlas_variables_description_<?php echo esc_attr( (string) $i ); ?>">
							<?php esc_html_e( 'Placeholder description', 'promptatlas' ); ?>
						</label>
						<input
							type="text"
							id="promptatlas_variables_description_<?php echo esc_attr( (string) $i ); ?>"
							name="promptatlas_variables[<?php echo esc_attr( (string) $i ); ?>][description]"
							value="<?php echo esc_attr( $description ); ?>"
							class="large-text"
							placeholder="<?php esc_attr_e( 'The role you are applying for, e.g. Senior Accountant', 'promptatlas' ); ?>"
						>
					</td>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render the example output panel.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function promptatlas_render_example_box( $post ) {
	$output = get_post_meta( $post->ID, PROMPTATLAS_META_EXAMPLE_OUTPUT, true );
	$tool   = get_post_meta( $post->ID, PROMPTATLAS_META_EXAMPLE_TOOL, true );

	?>
	<p class="description">
		<strong><?php esc_html_e( 'Only paste real output from a real run.', 'promptatlas' ); ?></strong>
		<?php esc_html_e( 'Never write an invented example. If you have not run the prompt yet, leave this empty — an empty field is honest, a made-up example is not.', 'promptatlas' ); ?>
	</p>

	<p>
		<label for="promptatlas_example_tool">
			<strong><?php esc_html_e( 'Tool used', 'promptatlas' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			id="promptatlas_example_tool"
			name="promptatlas_example_tool"
			value="<?php echo esc_attr( is_string( $tool ) ? $tool : '' ); ?>"
			class="regular-text"
			placeholder="<?php esc_attr_e( 'ChatGPT', 'promptatlas' ); ?>"
		>
		<span class="description"><?php esc_html_e( 'Required if you provide example output.', 'promptatlas' ); ?></span>
	</p>

	<p>
		<label for="promptatlas_example_output">
			<strong><?php esc_html_e( 'Example output', 'promptatlas' ); ?></strong>
		</label>
		<textarea
			id="promptatlas_example_output"
			name="promptatlas_example_output"
			rows="8"
			class="large-text code"
			style="font-family: Consolas, Monaco, monospace; line-height: 1.6;"
		><?php echo esc_textarea( is_string( $output ) ? $output : '' ); ?></textarea>
	</p>
	<?php
}

/**
 * Render the sourcing and related prompts panel.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function promptatlas_render_sourcing_box( $post ) {
	$attribution = get_post_meta( $post->ID, PROMPTATLAS_META_ATTRIBUTION, true );
	$related     = get_post_meta( $post->ID, PROMPTATLAS_META_RELATED, true );
	$related     = is_array( $related ) ? $related : array();

	?>
	<p>
		<label for="promptatlas_attribution">
			<strong><?php esc_html_e( 'Attribution', 'promptatlas' ); ?></strong>
		</label>
		<input
			type="text"
			id="promptatlas_attribution"
			name="promptatlas_attribution"
			value="<?php echo esc_attr( is_string( $attribution ) ? $attribution : '' ); ?>"
			class="widefat"
		>
		<span class="description">
			<?php esc_html_e( 'Leave empty if this prompt is original. Fill it in if adapted from somewhere else, including the source and its licence.', 'promptatlas' ); ?>
		</span>
	</p>

	<hr>

	<p>
		<label for="promptatlas_related">
			<strong><?php esc_html_e( 'Related prompts', 'promptatlas' ); ?></strong>
		</label>
		<input
			type="text"
			id="promptatlas_related"
			name="promptatlas_related"
			value="<?php echo esc_attr( implode( ', ', array_map( 'absint', $related ) ) ); ?>"
			class="widefat code"
			placeholder="112, 134"
		>
		<span class="description">
			<?php esc_html_e( 'Optional. Up to 3 prompt IDs, comma separated. These show before the automatic suggestions. Leave empty to use automatic suggestions only.', 'promptatlas' ); ?>
		</span>
	</p>
	<?php
}

/**
 * Render the collection prompt list panel.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function promptatlas_render_collection_box( $post ) {
	wp_nonce_field( PROMPTATLAS_NONCE_COLLECTION, PROMPTATLAS_NONCE_COLLECTION_FIELD );

	$prompt_ids = get_post_meta( $post->ID, PROMPTATLAS_META_COLLECTION_PROMPTS, true );
	$prompt_ids = is_array( $prompt_ids ) ? $prompt_ids : array();

	?>
	<p class="description">
		<?php esc_html_e( 'Prompt IDs in the order they should appear. The order is editorial and is shown to visitors exactly as entered.', 'promptatlas' ); ?>
	</p>

	<p>
		<label class="screen-reader-text" for="promptatlas_collection_prompts">
			<?php esc_html_e( 'Prompt IDs', 'promptatlas' ); ?>
		</label>
		<input
			type="text"
			id="promptatlas_collection_prompts"
			name="promptatlas_collection_prompts"
			value="<?php echo esc_attr( implode( ', ', array_map( 'absint', $prompt_ids ) ) ); ?>"
			class="large-text code"
			placeholder="112, 134, 156"
		>
	</p>

	<?php if ( ! empty( $prompt_ids ) ) : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th scope="col" style="width: 60px;"><?php esc_html_e( 'ID', 'promptatlas' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Prompt', 'promptatlas' ); ?></th>
					<th scope="col" style="width: 100px;"><?php esc_html_e( 'Status', 'promptatlas' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $prompt_ids as $prompt_id ) : ?>
					<?php $prompt_id = absint( $prompt_id ); ?>
					<tr>
						<td><code><?php echo esc_html( (string) $prompt_id ); ?></code></td>
						<td>
							<a href="<?php echo esc_url( (string) get_edit_post_link( $prompt_id ) ); ?>">
								<?php echo esc_html( get_the_title( $prompt_id ) ); ?>
							</a>
						</td>
						<td><?php echo esc_html( (string) get_post_status( $prompt_id ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<p class="description" style="margin-top: 12px;">
		<?php esc_html_e( 'Find prompt IDs in the Prompts list — the ID appears in the edit link for each prompt. A collection should contain at least 8 prompts before you publish it.', 'promptatlas' ); ?>
	</p>
	<?php
}

/**
 * Save the prompt meta fields.
 *
 * @param int     $post_id Post being saved.
 * @param WP_Post $post    Post object.
 * @return void
 */
function promptatlas_save_prompt_meta( $post_id, $post ) {
	if ( 'prompt' !== $post->post_type ) {
		return;
	}

	// Autosaves and revisions do not carry the meta box fields; writing here
	// would blank them.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	// The panel must actually have been submitted. Without this guard, a
	// programmatic save (WP-CLI, a quick edit, a REST call) would clear every
	// field because $_POST is empty.
	if ( ! isset( $_POST[ PROMPTATLAS_NONCE_PROMPT_FIELD ] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST[ PROMPTATLAS_NONCE_PROMPT_FIELD ] ) );

	if ( ! wp_verify_nonce( $nonce, PROMPTATLAS_NONCE_PROMPT ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// --- prompt text -------------------------------------------------------
	$prompt_text = isset( $_POST['promptatlas_prompt_text'] )
		? promptatlas_sanitize_multiline_text( wp_unslash( $_POST['promptatlas_prompt_text'] ) )
		: '';

	if ( '' === $prompt_text ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_PROMPT_TEXT );
	} else {
		update_post_meta( $post_id, PROMPTATLAS_META_PROMPT_TEXT, $prompt_text );
	}

	// --- variables ---------------------------------------------------------
	$variables = isset( $_POST['promptatlas_variables'] ) && is_array( $_POST['promptatlas_variables'] )
		? promptatlas_sanitize_variables( wp_unslash( $_POST['promptatlas_variables'] ) )
		: array();

	if ( empty( $variables ) ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_VARIABLES );
	} else {
		update_post_meta( $post_id, PROMPTATLAS_META_VARIABLES, $variables );
	}

	// --- example output and its tool ---------------------------------------
	$example_output = isset( $_POST['promptatlas_example_output'] )
		? promptatlas_sanitize_multiline_text( wp_unslash( $_POST['promptatlas_example_output'] ) )
		: '';

	$example_tool = isset( $_POST['promptatlas_example_tool'] )
		? sanitize_text_field( wp_unslash( $_POST['promptatlas_example_tool'] ) )
		: '';

	if ( '' === $example_output ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_EXAMPLE_OUTPUT );
		delete_post_meta( $post_id, PROMPTATLAS_META_EXAMPLE_TOOL );
	} else {
		update_post_meta( $post_id, PROMPTATLAS_META_EXAMPLE_OUTPUT, $example_output );

		if ( '' === $example_tool ) {
			delete_post_meta( $post_id, PROMPTATLAS_META_EXAMPLE_TOOL );
		} else {
			update_post_meta( $post_id, PROMPTATLAS_META_EXAMPLE_TOOL, $example_tool );
		}
	}

	// --- attribution -------------------------------------------------------
	$attribution = isset( $_POST['promptatlas_attribution'] )
		? sanitize_text_field( wp_unslash( $_POST['promptatlas_attribution'] ) )
		: '';

	if ( '' === $attribution ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_ATTRIBUTION );
	} else {
		update_post_meta( $post_id, PROMPTATLAS_META_ATTRIBUTION, $attribution );
	}

	// --- related overrides -------------------------------------------------
	$related_raw = isset( $_POST['promptatlas_related'] )
		? sanitize_text_field( wp_unslash( $_POST['promptatlas_related'] ) )
		: '';

	$related = promptatlas_sanitize_related_ids( $related_raw );

	// A prompt must never be related to itself.
	$related = array_values( array_diff( $related, array( (int) $post_id ) ) );

	if ( empty( $related ) ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_RELATED );
	} else {
		update_post_meta( $post_id, PROMPTATLAS_META_RELATED, $related );
	}
}
add_action( 'save_post_prompt', 'promptatlas_save_prompt_meta', 10, 2 );

/**
 * Save the collection prompt list.
 *
 * @param int     $post_id Post being saved.
 * @param WP_Post $post    Post object.
 * @return void
 */
function promptatlas_save_collection_meta( $post_id, $post ) {
	if ( 'collection' !== $post->post_type ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST[ PROMPTATLAS_NONCE_COLLECTION_FIELD ] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST[ PROMPTATLAS_NONCE_COLLECTION_FIELD ] ) );

	if ( ! wp_verify_nonce( $nonce, PROMPTATLAS_NONCE_COLLECTION ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['promptatlas_collection_prompts'] )
		? sanitize_text_field( wp_unslash( $_POST['promptatlas_collection_prompts'] ) )
		: '';

	$prompt_ids = promptatlas_sanitize_collection_prompts( $raw );

	if ( empty( $prompt_ids ) ) {
		delete_post_meta( $post_id, PROMPTATLAS_META_COLLECTION_PROMPTS );
	} else {
		update_post_meta( $post_id, PROMPTATLAS_META_COLLECTION_PROMPTS, $prompt_ids );
	}

	// Keep the reverse index on each prompt in step with this list.
	promptatlas_sync_collection_reverse_index( $post_id, $prompt_ids );
}
add_action( 'save_post_collection', 'promptatlas_save_collection_meta', 10, 2 );

/**
 * Add an ID column to the prompt list table.
 *
 * Collections are curated by prompt ID, so the ID has to be readable at a
 * glance rather than hunted for in each row's edit URL.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string> Columns with ID inserted.
 */
function promptatlas_add_id_column( $columns ) {
	$updated = array();

	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$updated['promptatlas_id'] = __( 'ID', 'promptatlas' );
		}

		$updated[ $key ] = $label;
	}

	return $updated;
}
add_filter( 'manage_prompt_posts_columns', 'promptatlas_add_id_column' );

/**
 * Render the ID column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function promptatlas_render_id_column( $column, $post_id ) {
	if ( 'promptatlas_id' !== $column ) {
		return;
	}

	echo '<code>' . esc_html( (string) $post_id ) . '</code>';
}
add_action( 'manage_prompt_posts_custom_column', 'promptatlas_render_id_column', 10, 2 );
