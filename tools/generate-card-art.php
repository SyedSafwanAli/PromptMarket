<?php
/**
 * PromptAtlas — generate abstract card artwork.
 *
 * Run with WP-CLI:
 *   C:\xampp\php\php.exe C:\xampp\wp-cli.phar eval-file tools/generate-card-art.php
 *   ... eval-file tools/generate-card-art.php --force
 *
 * ---------------------------------------------------------------------------
 * Why this replaced the typographic cards
 * ---------------------------------------------------------------------------
 * The first generation of card images carried the prompt title and category as
 * text. That suited a layout where the image was a header. It does not suit an
 * image-dominant grid, where the title sits in the meta row underneath: the
 * title would appear twice, a screen reader would meet it twice, and a long
 * title would be unreadable at 317px wide.
 *
 * So the artwork is now genuinely abstract — composition and colour only. The
 * image is the visual, the title is text. Each is derived deterministically
 * from the post ID, so a given prompt always gets the same artwork, but no two
 * adjacent cards look alike.
 *
 * Built from the brand palette and the bracket motif, so a wall of them still
 * reads as one site rather than as stock decoration.
 *
 * Output is 4:3 to match the grid, and quantised to a 128-colour palette — flat
 * graphics, so this is lossless and roughly a third of the truecolour size.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 'Run this through WP-CLI.' );
}

$promptatlas_force = in_array( '--force', (array) ( $args ?? array() ), true );

require_once __DIR__ . '/class-promptatlas-seeded-random.php';

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

/**
 * Accent colour per category, from the brand palette.
 *
 * @return array<string, array{0:int,1:int,2:int}> Slug to RGB.
 */
function promptatlas_art_colours() {
	return array(
		'writing-editing'       => array( 0x8b, 0x26, 0x26 ),
		'marketing-content'     => array( 0xef, 0x69, 0x05 ),
		'business-strategy'     => array( 0x48, 0x6c, 0x2f ),
		'coding-development'    => array( 0x6d, 0x1d, 0x1d ),
		'career-job-search'     => array( 0x8f, 0x3e, 0x02 ),
		'education-study'       => array( 0x35, 0x51, 0x1f ),
		'image-video'           => array( 0x6b, 0x5d, 0x10 ),
		'productivity-personal' => array( 0x4c, 0x46, 0x3d ),
	);
}

/**
 * Mix two colours.
 *
 * @param array{0:int,1:int,2:int} $a      First colour.
 * @param array{0:int,1:int,2:int} $b      Second colour.
 * @param float                    $amount How much of $b, 0 to 1.
 * @return array{0:int,1:int,2:int} The mix.
 */
function promptatlas_mix( array $a, array $b, $amount ) {
	return array(
		(int) round( $a[0] + ( $b[0] - $a[0] ) * $amount ),
		(int) round( $a[1] + ( $b[1] - $a[1] ) * $amount ),
		(int) round( $a[2] + ( $b[2] - $a[2] ) * $amount ),
	);
}

/**
 * Draw the bracket motif, scaled, with non-overlapping arms.
 *
 * @param GdImage $im     Image.
 * @param int     $x      Left.
 * @param int     $y      Top.
 * @param int     $size   Box size.
 * @param int     $colour Colour id.
 * @return void
 */
function promptatlas_art_motif( $im, $x, $y, $size, $colour ) {
	$u = $size / 32.0;
	$w = max( 2, (int) round( 3.2 * $u ) );

	$top    = (int) round( 4.5 * $u );
	$bottom = (int) round( 27.5 * $u );
	$height = $bottom - $top;

	$lx = (int) round( 7 * $u );
	imagefilledrectangle( $im, $x + $lx, $y + $top, $x + $lx + $w, $y + $top + $height, $colour );
	imagefilledrectangle( $im, $x + $lx + $w + 1, $y + $top, $x + (int) round( 12.5 * $u ), $y + $top + $w, $colour );
	imagefilledrectangle( $im, $x + $lx + $w + 1, $y + $top + $height - $w, $x + (int) round( 12.5 * $u ), $y + $top + $height, $colour );

	$rx = (int) round( 25 * $u );
	imagefilledrectangle( $im, $x + $rx - $w, $y + $top, $x + $rx, $y + $top + $height, $colour );
	imagefilledrectangle( $im, $x + (int) round( 19.5 * $u ), $y + $top, $x + $rx - $w - 1, $y + $top + $w, $colour );
	imagefilledrectangle( $im, $x + (int) round( 19.5 * $u ), $y + $top + $height - $w, $x + $rx - $w - 1, $y + $top + $height, $colour );
}

/**
 * Build one piece of card artwork.
 *
 * Five compositions, chosen by the seed. Each uses the same two ingredients —
 * the bracket motif and flat rectangles — so the set holds together while no
 * two neighbours repeat.
 *
 * @param int                      $seed   Post ID.
 * @param array{0:int,1:int,2:int} $accent Category accent.
 * @param string                   $path   Output path.
 * @return bool Whether it was written.
 */
function promptatlas_build_art( $seed, array $accent, $path ) {
	$w = 952;
	$h = 714; // 4:3.

	$im = imagecreatetruecolor( $w, $h );

	$paper = array( 0xfc, 0xfa, 0xf6 );
	$ink   = array( 0x1f, 0x1b, 0x16 );

	$rng = new PromptAtlas_Seeded_Random( $seed );

	// Background: the accent, heavily lightened towards paper, so the grid
	// stays airy and the artwork never competes with the text beneath it.
	$bg_mix = $rng->next( 86, 94 ) / 100;
	$bg     = imagecolorallocate( $im, ...promptatlas_mix( $accent, $paper, $bg_mix ) );
	imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

	$solid = imagecolorallocate( $im, ...$accent );
	$mid   = imagecolorallocate( $im, ...promptatlas_mix( $accent, $paper, 0.45 ) );
	$pale  = imagecolorallocate( $im, ...promptatlas_mix( $accent, $paper, 0.72 ) );
	$dark  = imagecolorallocate( $im, ...promptatlas_mix( $accent, $ink, 0.35 ) );

	$composition = $rng->next( 1, 5 );

	switch ( $composition ) {

		case 1:
			// Oversized motif, cropped by the frame.
			$size = $rng->next( 640, 820 );
			promptatlas_art_motif( $im, $rng->next( -80, 180 ), $rng->next( -90, 40 ), $size, $mid );
			imagefilledrectangle( $im, 0, $h - $rng->next( 40, 90 ), $w, $h, $solid );
			break;

		case 2:
			// Column field with one solid accent column.
			$cols = $rng->next( 5, 8 );
			$cw   = (int) ( $w / $cols );
			$hit  = $rng->next( 0, $cols - 1 );

			for ( $i = 0; $i < $cols; $i++ ) {
				$top = $rng->next( 0, (int) ( $h * 0.45 ) );
				imagefilledrectangle( $im, $i * $cw, $top, ( $i * $cw ) + $cw - 14, $h, $i === $hit ? $solid : $pale );
			}
			break;

		case 3:
			// Motif centred over a split field.
			imagefilledrectangle( $im, 0, 0, (int) ( $w * ( $rng->next( 35, 60 ) / 100 ) ), $h, $pale );
			promptatlas_art_motif( $im, (int) ( $w * 0.28 ), (int) ( $h * 0.14 ), $rng->next( 420, 520 ), $dark );
			break;

		case 4:
			// Stacked bars, one offset.
			$bars = $rng->next( 3, 5 );
			$bh   = (int) ( $h / ( $bars + 2 ) );

			for ( $i = 0; $i < $bars; $i++ ) {
				$y    = (int) ( $h * 0.18 ) + ( $i * ( $bh + 22 ) );
				$left = $rng->next( 40, 200 );
				imagefilledrectangle( $im, $left, $y, $left + $rng->next( 300, 760 ), $y + $bh, 0 === $i % 2 ? $mid : $pale );
			}

			imagefilledrectangle( $im, 0, 0, $rng->next( 40, 90 ), $h, $solid );
			break;

		default:
			// Corner block with a small motif.
			$bw = $rng->next( 300, 480 );
			imagefilledrectangle( $im, $w - $bw, 0, $w, $rng->next( 300, 500 ), $pale );
			imagefilledrectangle( $im, 0, $h - $rng->next( 180, 300 ), $rng->next( 240, 420 ), $h, $mid );
			promptatlas_art_motif( $im, (int) ( $w * 0.36 ), (int) ( $h * 0.3 ), $rng->next( 200, 300 ), $solid );
			break;
	}

	// Quantise: flat graphics, so this is lossless and roughly a third the size.
	$palette = imagecreatetruecolor( $w, $h );
	imagecopy( $palette, $im, 0, 0, 0, 0, $w, $h );
	imagetruecolortopalette( $palette, false, 128 );

	$ok = imagepng( $palette, $path, 9 );

	imagedestroy( $palette );
	imagedestroy( $im );

	return (bool) $ok;
}

/**
 * Attach artwork to a post as its featured image.
 *
 * @param int    $post_id Post ID.
 * @param string $path    Local PNG path.
 * @param string $title   Post title.
 * @return int Attachment ID, or 0.
 */
function promptatlas_attach_art( $post_id, $path, $title ) {
	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		return 0;
	}

	$dest = trailingslashit( $uploads['path'] ) . basename( $path );

	if ( ! copy( $path, $dest ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$dest,
		$post_id
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $dest );

	// Quantise the derived sizes too — the grid serves a derivative, not the
	// original, so quantising only the original saves nothing a visitor sees.
	if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
		$dir = trailingslashit( dirname( $dest ) );

		foreach ( $metadata['sizes'] as $size ) {
			if ( empty( $size['file'] ) ) {
				continue;
			}

			$size_path = $dir . $size['file'];

			if ( ! is_readable( $size_path ) ) {
				continue;
			}

			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- A derived size that is not a readable PNG should be skipped, not fatal.
			$img = @imagecreatefrompng( $size_path );

			if ( ! $img ) {
				continue;
			}

			$flat = imagecreatetruecolor( imagesx( $img ), imagesy( $img ) );
			imagecopy( $flat, $img, 0, 0, 0, 0, imagesx( $img ), imagesy( $img ) );
			imagetruecolortopalette( $flat, false, 128 );
			imagepng( $flat, $size_path, 9 );

			imagedestroy( $flat );
			imagedestroy( $img );
			clearstatcache( true, $size_path );
		}
	}

	wp_update_attachment_metadata( $attachment_id, $metadata );

	/*
	 * The artwork is decorative: it carries no information the page does not
	 * state in text, so an empty alt is correct. A description of the shapes
	 * would be noise to a screen reader user.
	 */
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', '' );

	set_post_thumbnail( $post_id, $attachment_id );

	return (int) $attachment_id;
}

// ---------------------------------------------------------------------------

$colours = promptatlas_art_colours();
$tmp_dir = get_temp_dir();

$posts = get_posts(
	array(
		'post_type'      => array( 'prompt', 'collection' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	)
);

$made   = 0;
$failed = 0;

foreach ( $posts as $post ) {

	if ( has_post_thumbnail( $post->ID ) && ! $promptatlas_force ) {
		continue;
	}

	if ( 'collection' === $post->post_type ) {
		$kicker = __( 'Collection', 'promptatlas' );
		$accent = array( 0x8b, 0x26, 0x26 );
	} else {
		$term   = function_exists( 'promptatlas_get_prompt_category' )
			? promptatlas_get_prompt_category( $post->ID )
			: null;
		$kicker = $term ? html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ) : __( 'Prompt', 'promptatlas' );
		$slug   = $term ? $term->slug : '';
		$accent = isset( $colours[ $slug ] ) ? $colours[ $slug ] : array( 0x8b, 0x26, 0x26 );
	}

	$title = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
	$file  = trailingslashit( $tmp_dir ) . 'pa-art-' . $post->ID . '.png';

	if ( ! promptatlas_build_art( $post->ID, $accent, $file ) ) {
		WP_CLI::warning( 'could not build art for #' . $post->ID );
		++$failed;
		continue;
	}

	if ( 0 === promptatlas_attach_art( $post->ID, $file, $title ) ) {
		WP_CLI::warning( 'could not attach art for #' . $post->ID );
		++$failed;
		wp_delete_file( $file );
		continue;
	}

	wp_delete_file( $file );
	++$made;
}

WP_CLI::success( sprintf( '%d artworks created, %d failed.', $made, $failed ) );
