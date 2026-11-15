<?php
/**
 * Freemius Deployment & Build Engine
 *
 * Builds production-ready ZIP packages:
 * 1. build/free/nexura-ai-search-optimizer.zip (100% WordPress.org compliant, zero pro/ code)
 * 2. build/pro/nexura-ai-search-optimizer-pro.zip (Complete bundle with pro/ features and premium flag)
 *
 * @package NexuraAISearch
 */

// phpcs:ignoreFile
if ( ! defined( 'ABSPATH' ) ) {
	if ( 'cli' === php_sapi_name() ) {
		define( 'ABSPATH', dirname( __DIR__ ) . '/' );
	}
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Fallback escaping helper when run via CLI outside WordPress.
	 *
	 * @param string $text Text to escape.
	 * @return string
	 */
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

$root_dir   = dirname( __DIR__ );
// Store build output outside the plugin directory to comply with Plugin Check (PCP) rules
$build_dir  = dirname( $root_dir ) . DIRECTORY_SEPARATOR . 'nexura-builds';
$free_dir   = $build_dir . DIRECTORY_SEPARATOR . 'free';
$pro_dir    = $build_dir . DIRECTORY_SEPARATOR . 'pro';

echo "========================================================\n";
echo " Nexura AI Search Optimizer - Build & Packaging System\n";
echo "========================================================\n\n";

// Ensure build directories exist
if ( ! is_dir( $free_dir ) ) {
	mkdir( $free_dir, 0777, true );
}
if ( ! is_dir( $pro_dir ) ) {
	mkdir( $pro_dir, 0777, true );
}

/**
 * Recursive copy with exclude filter.
 *
 * @param string   $src Source dir.
 * @param string   $dst Dest dir.
 * @param array    $excludes Excluded paths/names.
 * @return void
 */
function naso_copy_dir( $src, $dst, array $excludes = array(), $root = null ) {
	if ( null === $root ) {
		$root = $src;
	}
	$dir = opendir( $src );
	@mkdir( $dst, 0777, true );

	while ( false !== ( $file = readdir( $dir ) ) ) {
		if ( '.' === $file || '..' === $file ) {
			continue;
		}

		$src_file = $src . DIRECTORY_SEPARATOR . $file;
		$dst_file = $dst . DIRECTORY_SEPARATOR . $file;

		$rel_path = ltrim( str_replace( $root, '', $src_file ), '\\/' );
		$norm_rel = str_replace( '\\', '/', $rel_path );

		// Check exclusion against relative path from plugin root
		$skip = false;
		foreach ( $excludes as $exclude ) {
			if ( $file === $exclude || $norm_rel === $exclude || 0 === strpos( $norm_rel, $exclude . '/' ) ) {
				$skip = true;
				break;
			}
		}

		if ( $skip ) {
			continue;
		}

		if ( is_dir( $src_file ) ) {
			naso_copy_dir( $src_file, $dst_file, $excludes, $root );
		} else {
			copy( $src_file, $dst_file );
		}
	}
	closedir( $dir );
}

/**
 * Recursively remove a directory.
 *
 * @param string $dir Path.
 * @return void
 */
function naso_rmdir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach ( $files as $file ) {
		( is_dir( "$dir/$file" ) ) ? naso_rmdir( "$dir/$file" ) : unlink( "$dir/$file" );
	}
	rmdir( $dir );
}

/**
 * Create zip archive from directory.
 *
 * @param string $source_dir Directory to zip.
 * @param string $out_zip Output ZIP path.
 * @param string $root_folder Internal root folder name inside zip.
 * @return bool
 */
function naso_create_zip( $source_dir, $out_zip, $root_folder = 'nexura-ai-search-optimizer' ) {
	$temp_zip = $out_zip . '.' . uniqid( 'tmp_', true ) . '.zip';
	if ( file_exists( $temp_zip ) ) {
		@unlink( $temp_zip );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		return false;
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $source_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ( $files as $file ) {
		if ( ! $file->isDir() ) {
			$file_path = $file->getRealPath();
			$relative_path = substr( $file_path, strlen( realpath( $source_dir ) ) + 1 );
			$zip_entry = $root_folder . '/' . str_replace( '\\', '/', $relative_path );
			$zip->addFile( $file_path, $zip_entry );
		}
	}
	unset( $files );

	$closed = $zip->close();
	if ( ! $closed || ! file_exists( $temp_zip ) ) {
		return false;
	}

	if ( file_exists( $out_zip ) ) {
		@unlink( $out_zip );
	}

	return @rename( $temp_zip, $out_zip );
}

// Global excludes for both builds
$base_excludes = array(
	'.git',
	'.gitignore',
	'.gitattributes',
	'.distignore',
	'.github',
	'.agents',
	'.DS_Store',
	'Thumbs.db',
	'build',
	'tools',
	'tests',
	'docs',
	'node_modules',
	'FINAL_DEVELOPMENT_GUIDE.md',
);

// ----------------------------------------------------
// 1. Build Free Release (WordPress.org Compliant)
// ----------------------------------------------------
echo "-> Building FREE Edition (Excluding /pro/ entirely)...\n";
$free_stage = $free_dir . DIRECTORY_SEPARATOR . 'stage';
naso_rmdir( $free_stage );

$free_excludes = array_merge( $base_excludes, array( 'pro' ) );
naso_copy_dir( $root_dir, $free_stage, $free_excludes );

$free_zip_path = $free_dir . DIRECTORY_SEPARATOR . 'nexura-ai-search-optimizer.zip';
$res_free = naso_create_zip( $free_stage, $free_zip_path, 'nexura-ai-search-optimizer' );
naso_rmdir( $free_stage );

if ( $res_free && file_exists( $free_zip_path ) ) {
	$size_kb = round( filesize( $free_zip_path ) / 1024, 2 );
	echo '   [SUCCESS] Free ZIP created: ' . esc_html( $free_zip_path ) . ' (' . esc_html( (string) $size_kb ) . " KB)\n";
	echo "   [USE FOR] WordPress.org plugin submit ONLY.\n";
} else {
	echo "   [ERROR] Failed to create Free ZIP.\n";
}

// ----------------------------------------------------
// 2. Build Pro Release (Freemius Deployment Package)
// ----------------------------------------------------
echo "\n-> Building PRO / Freemius Package (Including /pro/ features)...\n";
$pro_stage = $pro_dir . DIRECTORY_SEPARATOR . 'stage';
naso_rmdir( $pro_stage );

naso_copy_dir( $root_dir, $pro_stage, $base_excludes );

$pro_zip_path = $pro_dir . DIRECTORY_SEPARATOR . 'nexura-ai-search-optimizer-pro.zip';
$res_pro = naso_create_zip( $pro_stage, $pro_zip_path, 'nexura-ai-search-optimizer' );
naso_rmdir( $pro_stage );

// Update is_premium => true inside the ZipArchive directly without writing PHP files to disk
if ( $res_pro && file_exists( $pro_zip_path ) ) {
	$zip_mod = new ZipArchive();
	if ( true === $zip_mod->open( $pro_zip_path ) ) {
		$entry_name = 'nexura-ai-search-optimizer/nexura-ai-search-optimizer.php';
		$main_content = $zip_mod->getFromName( $entry_name );
		if ( false !== $main_content ) {
			$main_content = str_replace( "'is_premium'          => false,", "'is_premium'          => true,", $main_content );
			$zip_mod->addFromString( $entry_name, $main_content );
		}
		$zip_mod->close();
	}
}

if ( $res_pro && file_exists( $pro_zip_path ) ) {
	$size_kb = round( filesize( $pro_zip_path ) / 1024, 2 );
	echo '   [SUCCESS] Pro ZIP created: ' . esc_html( $pro_zip_path ) . ' (' . esc_html( (string) $size_kb ) . " KB)\n";
	echo "   [USE FOR] Freemius Dashboard -> Deployment -> Add New Version.\n";
	echo "   [NOTE] Never upload the raw plugin folder (it contains build/*.zip).\n";
} else {
	echo "   [ERROR] Failed to create Pro ZIP.\n";
}

echo "\nBuild Process Complete!\n";
echo "WordPress.org : build/free/nexura-ai-search-optimizer.zip\n";
echo "Freemius      : build/pro/nexura-ai-search-optimizer-pro.zip\n";
echo "Do NOT zip E:\\...\\nexura-ai-search-optimizer directly.\n";
