<?php
/**
 * PromptAtlas — generate editorial featured images.
 *
 * Run with WP-CLI:
 *   C:\xampp\php\php.exe C:\xampp\wp-cli.phar eval-file tools/generate-featured-images.php
 *
 * Add --force to regenerate images that already exist:
 *   ... eval-file tools/generate-featured-images.php --force
 *
 * ---------------------------------------------------------------------------
 * Why generated images rather than photographs
 * ---------------------------------------------------------------------------
 * A prompt page has nothing to photograph. The alternatives were:
 *
 *   - Stock photography. Generic, licensed, and dishonest — a smiling person at
 *     a laptop tells the reader nothing about the prompt and makes the site look
 *     like every content farm.
 *   - AI-generated illustration. Inconsistent across 26 images, expensive to
 *     redo, and impossible to keep on-brand without constant supervision.
 *   - Typographic cards built from the design tokens. Consistent, instant to
 *     regenerate when a title changes, genuinely on-brand, and honest: the image
 *     shows what the page is, which is a piece of writing.
 *
 * The third is what editorial sites with no photography actually do, so that is
 * what this builds. Each category gets its own accent colour so a grid of cards
 * has real variety rather than 26 identical rectangles.
 *
 * Images are 1200x630, which doubles as the Open Graph size — so a shared
 * prompt gets its own card rather than the site-wide default.
 *
 * @package PromptAtlas
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 'Run this through WP-CLI.' );
}

$promptatlas_force = in_array( '--force', (array) ( $args ?? array() ), true );

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

/**
 * Accent colour per category, drawn from the brand palette.
 *
 * Each is a colour the design system already uses, so nothing here introduces a
 * value that does not exist in tokens.css.
 *
 * @return array<string, array{0:int,1:int,2:int}> Slug to RGB.
 */
function promptatlas_category_colours() {
	return array(
		'writing-editing'       => array( 0x8b, 0x26, 0x26 ), // burgundy
		'marketing-content'     => array( 0xef, 0x69, 0x05 ), // orange
		'business-strategy'     => array( 0x48, 0x6c, 0x2f ), // olive
		'coding-development'    => array( 0x6d, 0x1d, 0x1d ), // burgundy deep
		'career-job-search'     => array( 0x8f, 0x3e, 0x02 ), // orange deep
		'education-study'       => array( 0x35, 0x51, 0x1f ), // olive deep
		'image-video'           => array( 0x6b, 0x5d, 0x10 ), // butter deep
		'productivity-personal' => array( 0x4c, 0x46, 0x3d ), // warm neutral
	);
}

/**
 * Allocate a colour.
 *
 * @param GdImage                  $im  Image.
 * @param array{0:int,1:int,2:int} $rgb RGB triplet.
 * @return int Colour id.
 */
function promptatlas_colour( $im, array $rgb ) {
	return (int) imagecolorallocate( $im, $rgb[0], $rgb[1], $rgb[2] );
}

/**
 * Wrap text to a pixel width using the real font metrics.
 *
 * @param string $text     The text.
 * @param string $font     TTF path.
 * @param int    $size     Point size.
 * @param int    $max_width Maximum width in pixels.
 * @param int    $max_lines Maximum lines; the last is ellipsised.
 * @return string[] Lines.
 */
function promptatlas_wrap( $text, $font, $size, $max_width, $max_lines ) {
	$words = preg_split( '/\s+/', trim( $text ) );
	$lines = array();
	$line  = '';

	foreach ( (array) $words as $word ) {
		$try = ( '' === $line ) ? $word : $line . ' ' . $word;
		$box = imagettfbbox( $size, 0, $font, $try );

		if ( is_array( $box ) && ( $box[2] - $box[0] ) > $max_width && '' !== $line ) {
			$lines[] = $line;
			$line    = $word;

			if ( count( $lines ) === $max_lines ) {
				break;
			}
		} else {
			$line = $try;
		}
	}

	if ( count( $lines ) < $max_lines && '' !== $line ) {
		$lines[] = $line;
	}

	// If the text overflowed, mark the final line so it does not look truncated
	// by accident.
	if ( count( $lines ) === $max_lines ) {
		$joined = implode( ' ', $lines );

		if ( strlen( $joined ) < strlen( trim( $text ) ) ) {
			$lines[ $max_lines - 1 ] = rtrim( $lines[ $max_lines - 1 ], ' ,.' ) . '…';
		}
	}

	return $lines;
}

/**
 * Draw the bracket motif.
 *
 * The same mark used site-wide, scaled up and bleeding off the right edge as a
 * background element. Drawn with filled rectangles so the edges stay crisp.
 *
 * @param GdImage $im     Image.
 * @param int     $x      Left of the motif box.
 * @param int     $y      Top of the motif box.
 * @param int     $size   Box size.
 * @param int     $colour Colour id.
 * @return void
 */
function promptatlas_draw_motif( $im, $x, $y, $size, $colour ) {
	$u = $size / 32.0;
	$w = (int) round( 3.2 * $u );

	$top    = (int) round( 4.5 * $u );
	$bottom = (int) round( 27.5 * $u );
	$height = $bottom - $top;

	/*
	 * The arms deliberately start AFTER the vertical bar ends, rather than
	 * overlapping it. With a semi-transparent colour an overlap blends twice
	 * and shows as a darker patch at each corner, which made the motif look
	 * blotchy rather than solid.
	 */
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
 * Build one featured image.
 *
 * @param string                   $title    Post title.
 * @param string                   $kicker   Category or "Collection".
 * @param array{0:int,1:int,2:int} $accent   Accent RGB.
 * @param string                   $path     Output file path.
 * @return bool Whether it was written.
 */
function promptatlas_build_image( $title, $kicker, array $accent, $path ) {
	$font_bold    = 'C:/Windows/Fonts/arialbd.ttf';
	$font_regular = 'C:/Windows/Fonts/arial.ttf';

	if ( ! is_readable( $font_bold ) || ! is_readable( $font_regular ) ) {
		return false;
	}

	$w = 1200;
	$h = 630;

	$im = imagecreatetruecolor( $w, $h );

	$bg       = promptatlas_colour( $im, array( 0xfc, 0xfa, 0xf6 ) );
	$charcoal = promptatlas_colour( $im, array( 0x1f, 0x1b, 0x16 ) );
	$muted    = promptatlas_colour( $im, array( 0x5c, 0x56, 0x4d ) );
	$accent_c = promptatlas_colour( $im, $accent );

	imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

	/*
	 * A tinted panel down the right side, with the bracket motif sitting in it
	 * and bleeding off the edge. This is what gives each card its identity
	 * without resorting to decoration for its own sake.
	 */
	$panel = imagecolorallocatealpha( $im, $accent[0], $accent[1], $accent[2], 108 );
	imagefilledrectangle( $im, (int) ( $w * 0.68 ), 0, $w, $h, (int) $panel );

	$motif = imagecolorallocatealpha( $im, $accent[0], $accent[1], $accent[2], 72 );
	promptatlas_draw_motif( $im, (int) ( $w * 0.72 ), 150, 400, (int) $motif );

	// Top rule, accent on the left.
	imagefilledrectangle( $im, 0, 0, $w, 12, $charcoal );
	imagefilledrectangle( $im, 0, 0, 200, 12, $accent_c );

	$margin = 80;

	// Wordmark.
	promptatlas_draw_motif( $im, $margin, 70, 44, $accent_c );
	imagettftext( $im, 21, 0, $margin + 60, 102, $charcoal, $font_bold, 'PromptAtlas' );

	// Kicker.
	$kicker_text = strtoupper( $kicker );
	imagettftext( $im, 17, 0, $margin, 215, $accent_c, $font_bold, $kicker_text );

	// Title, wrapped on real metrics.
	$max_width = (int) ( $w * 0.62 ) - $margin;
	$size      = 48;
	$lines     = promptatlas_wrap( $title, $font_bold, $size, $max_width, 4 );

	// Drop a size if it filled every line, so four-line titles stay comfortable.
	if ( count( $lines ) >= 4 ) {
		$size  = 40;
		$lines = promptatlas_wrap( $title, $font_bold, $size, $max_width, 4 );
	}

	$y = 290;

	foreach ( $lines as $line ) {
		imagettftext( $im, $size, 0, $margin, $y, $charcoal, $font_bold, $line );
		$y += (int) ( $size * 1.35 );
	}

	// Footer line.
	imagettftext( $im, 16, 0, $margin, $h - 60, $muted, $font_regular, 'Written by hand, and explained.' );
	imagefilledrectangle( $im, $margin, $h - 44, $margin + 90, $h - 41, $accent_c );

	$ok = imagepng( $im, $path, 9 );
	imagedestroy( $im );

	return (bool) $ok;
}

/**
 * Attach a generated image to a post as its featured image.
 *
 * @param int    $post_id Post ID.
 * @param string $path    Absolute path to the PNG.
 * @param string $title   Post title, used for the alt text.
 * @param string $kicker  Category name.
 * @return int Attachment ID, or 0.
 */
function promptatlas_attach_image( $post_id, $path, $title, $kicker ) {
	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		return 0;
	}

	$filename = basename( $path );
	$dest     = trailingslashit( $uploads['path'] ) . $filename;

	if ( ! copy( $path, $dest ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$dest,
		$post_id
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $dest )
	);

	/*
	 * Alt text. The image is typographic, so the alt describes what it is
	 * rather than repeating the title, which a screen reader has already read
	 * from the heading beside it.
	 */
	update_post_meta(
		$attachment_id,
		'_wp_attachment_image_alt',
		sprintf(
			/* translators: %s: category name. */
			__( 'PromptAtlas card for a prompt in %s', 'promptatlas' ),
			$kicker
		)
	);

	set_post_thumbnail( $post_id, $attachment_id );

	return (int) $attachment_id;
}

// ---------------------------------------------------------------------------
// Run
// ---------------------------------------------------------------------------

$colours = promptatlas_category_colours();
$tmp_dir = get_temp_dir();

$posts = get_posts(
	array(
		'post_type'      => array( 'prompt', 'collection' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	)
);

$made    = 0;
$skipped = 0;
$failed  = 0;

foreach ( $posts as $post ) {

	if ( has_post_thumbnail( $post->ID ) && ! $promptatlas_force ) {
		++$skipped;
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
	$file  = trailingslashit( $tmp_dir ) . 'pa-card-' . $post->ID . '.png';

	if ( ! promptatlas_build_image( $title, $kicker, $accent, $file ) ) {
		WP_CLI::warning( 'could not build image for #' . $post->ID );
		++$failed;
		continue;
	}

	$attachment_id = promptatlas_attach_image( $post->ID, $file, $title, $kicker );

	wp_delete_file( $file );

	if ( 0 === $attachment_id ) {
		WP_CLI::warning( 'could not attach image for #' . $post->ID );
		++$failed;
		continue;
	}

	++$made;
	WP_CLI::log( sprintf( '  #%d  %-10s  %s', $post->ID, $kicker, $title ) );
}

WP_CLI::success( sprintf( '%d images created, %d skipped, %d failed.', $made, $skipped, $failed ) );
