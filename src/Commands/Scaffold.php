<?php

namespace tad\WPCLI\Commands;


use tad\WPCLI\Exceptions\MissingRequirementException;
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

	public function __construct( Composer $composer = null ) {
		$this->composer = $composer ?: new Composer();
	}

	public function help() {
		\WP_CLI::line( 'usage: wp wpb-scaffold plugin-tests' );
		\WP_CLI::line( '   or: wp wpb-scaffold theme-tests' );
	}

	/**
	 * @alias plugin-tests
	 *
	 * @param array $args
	 * @param array $assocArgs
	 */
	public function pluginTests( array $args, array $assocArgs ) {
		$this->args      = $args;
		$this->assocArgs = $assocArgs;

		try {
			$this->composer->ensureComposer( $this->assocArgs );
		} catch ( MissingRequirementException $e ) {
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
