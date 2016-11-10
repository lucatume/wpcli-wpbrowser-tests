<?php

use tad\WPCLI\Commands\Scaffold;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if(file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once 'vendor/autoload.php';
}

WP_CLI::add_command( 'wpb-scaffold', new Scaffold(), array(
	'shortdesc' => 'Scaffolds wp-browser based tests for a plugin or theme',
	'synopsis'  => array(
		array(
			'type'     => 'positional',
			'name'     => 'subcommand',
			'optional' => false,
			'multiple' => false,
		),
		array(
			'type'     => 'positional',
			'name'     => 'slug',
			'optional' => true,
			'multiple' => false,
		),
		array(
			'type'     => 'flag',
			'name'     => 'dry-run',
			'optional' => true,
		),
		array(
			'type'     => 'flag',
			'name'     => 'install',
			'optional' => true,
		),
		array(
			'type'     => 'flag',
			'name'     => 'skip-composer-update',
			'optional' => true,
		),
		array(
			'type'     => 'assoc',
			'name'     => 'composer',
			'optional' => true,
		),
		array(
			'type'     => 'assoc',
			'name'     => 'slug',
			'optional' => true,
		),
		array(
			'type'     => 'assoc',
			'name'     => 'dir',
			'optional' => true,
		),
		array(
			'type'     => 'assoc',
			'name'     => 'description',
			'optional' => true,
		),
		array(
			'type'     => 'assoc',
			'name'     => 'name',
			'optional' => true,
		),
		array(
			'type'     => 'assoc',
			'name'     => 'email',
			'optional' => true,
		),
	)
) );
