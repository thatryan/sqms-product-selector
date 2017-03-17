<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Sqms_Product_Selector
 * Read More:
 * https://seravo.fi/2015/continuous-integration-testing-for-wordpress-plugins-on-github-using-travis-ci
 * https://ben.lobaugh.net/blog/84669/how-to-add-unit-testing-and-continuous-integration-to-your-wordpress-plugin
 * https://github.com/kevindees/travis-ci-wordpress
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/sqms-product-selector.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
