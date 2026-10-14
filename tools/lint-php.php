<?php
/**
 * PromptAtlas — PHP syntax check.
 *
 * Runs `php -l` over every PHP file this project authors: the promptatlas theme
 * and the promptatlas-core plugin. WordPress core, third-party code and Composer
 * dependencies are never checked.
 *
 * Written in PHP rather than as a shell script on purpose: it behaves identically
 * in Git Bash, PowerShell and CMD, and it uses the PHP binary that is actually
 * running it, so there is no hardcoded interpreter path to drift.
 *
 * Usage:
 *   C:\xampp\php\php.exe tools/lint-php.php
 *   composer lint
 *
 * Exit codes: 0 = every file is syntactically valid, 1 = at least one failed.
 *
 * @package PromptAtlas
 */

declare( strict_types = 1 );

// This is a command-line tool and must never be reachable over HTTP.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'This script may only be run from the command line.' );
}

$root = dirname( __DIR__ );

$targets = array(
	'wp-content/themes/promptatlas',
	'wp-content/plugins/promptatlas-core',
	'tools',
);

$skip_dirs = array( 'vendor', 'node_modules', '.git', 'dist', 'build' );

/**
 * Collect every .php file under a directory, skipping dependency folders.
 *
 * @param string   $dir       Absolute directory path.
 * @param string[] $skip_dirs Directory names to skip.
 * @return string[] Absolute file paths.
 */
function promptatlas_collect_php_files( string $dir, array $skip_dirs ): array {
	if ( ! is_dir( $dir ) ) {
		return array();
	}

	$files = array();

	$iterator = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
			static function ( SplFileInfo $current ) use ( $skip_dirs ): bool {
				if ( $current->isDir() ) {
					return ! in_array( $current->getFilename(), $skip_dirs, true );
				}
				return 'php' === strtolower( $current->getExtension() );
			}
		)
	);

	foreach ( $iterator as $file ) {
		if ( $file->isFile() ) {
			$files[] = $file->getPathname();
		}
	}

	sort( $files );

	return $files;
}

$php_binary = PHP_BINARY;
$all_files  = array();
$missing    = array();

foreach ( $targets as $target ) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $target );

	if ( ! is_dir( $path ) ) {
		$missing[] = $target;
		continue;
	}

	$all_files = array_merge( $all_files, promptatlas_collect_php_files( $path, $skip_dirs ) );
}

echo 'PromptAtlas PHP syntax check' . PHP_EOL;
echo 'PHP ' . PHP_VERSION . ' — ' . $php_binary . PHP_EOL;
echo str_repeat( '-', 60 ) . PHP_EOL;

foreach ( $missing as $target ) {
	echo 'skipped (does not exist yet): ' . $target . PHP_EOL;
}

if ( empty( $all_files ) ) {
	echo PHP_EOL . 'No PHP files found in the project source directories.' . PHP_EOL;
	echo 'Nothing to check. This is expected until the theme and plugin exist.' . PHP_EOL;
	exit( 0 );
}

$failed = array();

foreach ( $all_files as $file ) {
	$relative = ltrim( str_replace( array( $root, '\\' ), array( '', '/' ), $file ), '/' );

	$output       = array();
	$exit_code    = 0;
	$lint_command = escapeshellarg( $php_binary ) . ' -l -d display_errors=1 ' . escapeshellarg( $file );

	exec( $lint_command . ' 2>&1', $output, $exit_code );

	if ( 0 === $exit_code ) {
		echo '  OK    ' . $relative . PHP_EOL;
		continue;
	}

	$failed[ $relative ] = trim( implode( PHP_EOL, $output ) );
	echo '  FAIL  ' . $relative . PHP_EOL;
}

echo str_repeat( '-', 60 ) . PHP_EOL;

$checked = count( $all_files );

if ( empty( $failed ) ) {
	echo sprintf( 'PASS — %d file%s, no syntax errors.', $checked, 1 === $checked ? '' : 's' ) . PHP_EOL;
	exit( 0 );
}

echo sprintf( 'FAIL — %d of %d file%s had syntax errors:', count( $failed ), $checked, 1 === $checked ? '' : 's' ) . PHP_EOL . PHP_EOL;

foreach ( $failed as $relative => $message ) {
	echo $relative . PHP_EOL;
	echo '  ' . str_replace( PHP_EOL, PHP_EOL . '  ', $message ) . PHP_EOL . PHP_EOL;
}

exit( 1 );
