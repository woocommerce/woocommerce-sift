<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Sift_For_WooCommerce
 *
 * phpcs:disable
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	// WooCommerce is loaded from wp-env environment from the plugin directory.
	// Let's check if WOO_TEST_DIR is defined and load WooCommerce from there if it is.
	if ( false !== getenv( 'WOO_TEST_DIR' ) ) {
		require getenv( 'WOO_TEST_DIR' ) . '/woocommerce.php';
	} else {
		require dirname( dirname( __DIR__ ) ) . '/woocommerce/woocommerce.php';
	}
	require dirname( __DIR__ ) . '/sift-for-woocommerce.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
