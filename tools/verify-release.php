<?php
/**
 * Release Verification Script
 *
 * Verifies integrity of Free and Pro zip packages.
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
$build_dir  = dirname( $root_dir ) . DIRECTORY_SEPARATOR . 'nexura-builds';
$free_zip   = $build_dir . '/free/nexura-ai-search-optimizer.zip';
$pro_zip    = $build_dir . '/pro/nexura-ai-search-optimizer-pro.zip';

echo "========================================================\n";
echo " Nexura AI Search Optimizer - Release Integrity Audit\n";
echo "========================================================\n\n";

$errors = 0;

// 1. Verify Free Zip
echo "1. Auditing Free Edition ZIP:\n";
if ( ! file_exists( $free_zip ) ) {
	echo '   [FAIL] Free zip not found at ' . esc_html( $free_zip ) . "\n";
	$errors++;
} else {
	$zip = new ZipArchive();
	if ( true === $zip->open( $free_zip ) ) {
		$free_entries = array();
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$free_entries[] = $zip->getNameIndex( $i );
		}
		$zip->close();

		// Check for trialware leakage (MUST NOT contain /pro/)
		$leaked_pro = array_filter( $free_entries, function( $name ) {
			return strpos( $name, '/pro/' ) !== false;
		} );

		if ( ! empty( $leaked_pro ) ) {
			echo '   [FAIL] CRITICAL: Pro files leaked into Free ZIP! (' . intval( count( $leaked_pro ) ) . " files)\n";
			$errors++;
		} else {
			echo "   [PASS] Zero Pro code leakage. 100% compliant with WordPress.org Guidelines.\n";
		}

		// Dev-only paths must never ship in the WordPress.org Free ZIP.
		$forbidden_dev = array_filter( $free_entries, function( $name ) {
			return (bool) preg_match( '#/(tools|docs|tests|build|\.git)/#', $name );
		} );

		if ( ! empty( $forbidden_dev ) ) {
			echo '   [FAIL] CRITICAL: Dev-only paths found in Free ZIP! Examples: ' . esc_html( implode( ', ', array_slice( $forbidden_dev, 0, 5 ) ) ) . "\n";
			$errors++;
		} else {
			echo "   [PASS] Zero tools/docs/tests/build paths (WordPress.org package hygiene).\n";
		}

		// Check for forbidden nested zip or archive files
		$nested_zips = array_filter( $free_entries, function( $name ) {
			return preg_match( '/\.(zip|tar|gz|rar|7z)$/i', $name );
		} );

		if ( ! empty( $nested_zips ) ) {
			echo '   [FAIL] CRITICAL: Forbidden nested archive files found in Free ZIP! (' . esc_html( implode( ', ', $nested_zips ) ) . ")\n";
			$errors++;
		} else {
			echo "   [PASS] Zero nested ZIP archives (100% compliant with WordPress.org).\n";
		}

		// Check mandatory files
		$mandatory = array(
			'nexura-ai-search-optimizer/nexura-ai-search-optimizer.php',
			'nexura-ai-search-optimizer/readme.txt',
			'nexura-ai-search-optimizer/LICENSE.txt',
			'nexura-ai-search-optimizer/includes/class-plugin.php',
			'nexura-ai-search-optimizer/admin/class-admin.php',
			'nexura-ai-search-optimizer/vendor/freemius/start.php',
		);

		// Verify uninstall.php is NOT present (Freemius strict requirement)
		if ( in_array( 'nexura-ai-search-optimizer/uninstall.php', $free_entries, true ) ) {
			echo "   [FAIL] uninstall.php detected! Freemius requires after_uninstall hook instead of uninstall.php.\n";
			$errors++;
		} else {
			echo "   [PASS] Zero uninstall.php file (Compliant with Freemius deployment rules).\n";
		}

		$missing = array();
		foreach ( $mandatory as $man_file ) {
			if ( ! in_array( $man_file, $free_entries, true ) ) {
				$missing[] = $man_file;
			}
		}

		if ( ! empty( $missing ) ) {
			echo '   [FAIL] Missing mandatory files in Free ZIP: ' . esc_html( implode( ', ', $missing ) ) . "\n";
			$errors++;
		} else {
			echo '   [PASS] All mandatory plugin files present (' . intval( count( $free_entries ) ) . " total files).\n";
		}
	} else {
		echo "   [FAIL] Unable to open Free zip.\n";
		$errors++;
	}
}

// 2. Verify Pro Zip
echo "\n2. Auditing Pro Edition ZIP:\n";
if ( ! file_exists( $pro_zip ) ) {
	echo '   [FAIL] Pro zip not found at ' . esc_html( $pro_zip ) . "\n";
	$errors++;
} else {
	$zip = new ZipArchive();
	if ( true === $zip->open( $pro_zip ) ) {
		$pro_entries = array();
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$pro_entries[] = $zip->getNameIndex( $i );
		}

		// Check pro files exist
		$has_pro_core = in_array( 'nexura-ai-search-optimizer/pro/class-pro.php', $pro_entries, true );
		$has_bulk     = in_array( 'nexura-ai-search-optimizer/pro/class-bulk-audit.php', $pro_entries, true );
		$has_analytic = in_array( 'nexura-ai-search-optimizer/pro/class-bot-analytics.php', $pro_entries, true );

		if ( $has_pro_core && $has_bulk && $has_analytic ) {
			echo '   [PASS] Pro modules verified present (' . intval( count( $pro_entries ) ) . " total files).\n";
		} else {
			echo "   [FAIL] Missing required Pro modules in Pro ZIP.\n";
			$errors++;
		}

		// Check is_premium flag
		$main_code = $zip->getFromName( 'nexura-ai-search-optimizer/nexura-ai-search-optimizer.php' );
		$zip->close();

		if ( strpos( $main_code, "'is_premium'          => true" ) !== false ) {
			echo "   [PASS] Pro package correctly configured with is_premium => true.\n";
		} else {
			echo "   [FAIL] Pro package does not have is_premium => true flag.\n";
			$errors++;
		}
	} else {
		echo "   [FAIL] Unable to open Pro zip.\n";
		$errors++;
	}
}

echo "\n--------------------------------------------------------\n";
if ( 0 === $errors ) {
	echo "AUDIT RESULT: ALL CHECKS PASSED PERFECTLY (0 ERRORS).\n";
	echo "Ready for WordPress.org SVN and Freemius Deployment.\n";
} else {
	echo 'AUDIT RESULT: FAILED WITH ' . intval( $errors ) . " ERRORS.\n";
}
echo "========================================================\n";

exit( $errors > 0 ? 1 : 0 );
