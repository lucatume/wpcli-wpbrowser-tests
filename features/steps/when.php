<?php

use WP_CLI\Process;

function invoke_proc( $proc, $mode ) {
	$map    = array(
		'run' => 'run_check',
		'try' => 'run'
	);
	$method = $map[ $mode ];

	return $proc->$method();
}

$steps->When( '/^I launch in the background `([^`]+)`$/', function ( $world, $cmd ) {
	$world->background_proc( $cmd );
} );

$steps->When( '/^I (run|try) `([^`]+)`$/', function ( $world, $mode, $cmd ) {
	$cmd = $world->replace_variables( $cmd );

	if ( isset( $world->variables['appendParameter'] ) ) {
		$cmd .= $world->variables['appendParameter'];

		unset( $world->variables['appendParameter'] );
	}

	$world->result = invoke_proc( $world->proc( $cmd ), $mode );
} );

$steps->When( "/^I (run|try) `([^`]+)` from '([^\s]+)'$/", function ( $world, $mode, $cmd, $subdir ) {
	$cmd           = $world->replace_variables( $cmd );
	$world->result = invoke_proc( $world->proc( $cmd, array(), $subdir ), $mode );
} );

$steps->When( '/^I (run|try) the previous command again$/', function ( $world, $mode ) {
	if ( ! isset( $world->result ) ) {
		throw new \Exception( 'No previous command.' );
	}

	$proc          = Process::create( $world->result->command, $world->result->cwd, $world->result->env );
	$world->result = invoke_proc( $proc, $mode );
} );

$steps->When( '/^I (run|try) `([^`]+)` from data folder$/', function ( $world, $mode, $cmd ) {
	$cmd           = $world->replace_variables( $cmd );
	$world->result = invoke_proc( $world->proc( $cmd, array(), $world->get_data_dir() ), $mode );
} );

$steps->When( '/^I run `([^`]+)` with input$/', function ( $world, $cmd ) {
	/** @var FeatureContext $world */
	$mockInput     = implode( "\n", $world->variables['input'] ) . "\n";

	$world->result = $world->proc( $cmd )->run_with_input( $mockInput );
} );

