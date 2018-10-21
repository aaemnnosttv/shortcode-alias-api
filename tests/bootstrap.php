<?php

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/**
 * If PHPUnit is run from a different working directory, the relative path to our tests config file
 * will not exist. This corrects that by updating it with an absolute path if necessary.
 */
if (! file_exists(getenv('WP_PHPUNIT__TESTS_CONFIG'))) {
    putenv(sprintf('WP_PHPUNIT__TESTS_CONFIG=%s/%s', dirname(__DIR__), getenv('WP_PHPUNIT__TESTS_CONFIG')));
}

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function() {
    require dirname( dirname( __FILE__ ) ) . '/shortcode-alias-api.php';
} );

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
