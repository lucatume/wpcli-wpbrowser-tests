<?php

namespace tad\WPCLI\Commands;


use tad\WPCLI\Exceptions\BaseException;
use tad\WPCLI\System\Composer;

class Scaffold extends \WP_CLI_Command {

	/**
	 * @var array
	 */
	protected $args = array();

	/**
	 * @var array
	 */
	protected $assocArgs = array();

	/**
	 * @var bool
	 */
	protected $castExceptionsToErrors = true;

	/**
	 * @var Composer
	 */
	protected $composer;

	/**
	 * @var PluginTests
	 */
	protected $pluginTests;

	public function __construct( Composer $composer = null, PluginTests $pluginTests = null ) {
		$this->composer    = $composer ?: new Composer();
		$this->pluginTests = $pluginTests ?: new PluginTests();
	}


	public function __invoke( array $args = array(), array $assocArgs = array() ) {
		$subcommand = ! empty( $args[0] ) ? $args[0] : false;
		if ( ! $subcommand ) {
			return $this->help();
		}

		switch ( $subcommand ) {
			case 'plugin-tests':
				return $this->pluginTests( $args, $assocArgs );
			default:
				return $this->help();
		}
	}

	public function help() {
		\WP_CLI::line( 'usage: wp wpb-scaffold plugin-tests my-plugin' );
		\WP_CLI::line( '   or: wp wpb-scaffold theme-tests my-theme' );
	}

	/**
	 * @subcommand plugin-tests
	 *
	 * @param array $args
	 * @param array $assocArgs
	 */
	public function pluginTests( array $args, array $assocArgs ) {
		$this->args      = $args;
		$this->assocArgs = $assocArgs;

		try {
			$this->composer->ensureComposer( $this->assocArgs );
			$this->pluginTests->scaffold( $args, $assocArgs );
		} catch ( BaseException $e ) {
			if ( $this->castExceptionsToErrors ) {
				\WP_CLI::error( $e->getMessage(), 0 );

				return false;
			}
			throw $e;
		}
	}

	/**
	 * @param boolean $castExceptionsToErrors
	 */
	public function setCastExceptionsToErrors( $castExceptionsToErrors ) {
		$this->castExceptionsToErrors = $castExceptionsToErrors;
	}
}
