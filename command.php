<?php

use tad\WPCLI\Commands\Scaffold;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

require_once 'vendor/autoload.php';

$scaffoldCommand = new Scaffold();

WP_CLI::add_command( 'wpb-scaffold', $scaffoldCommand );
