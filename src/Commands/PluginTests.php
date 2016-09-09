<?php

namespace tad\WPCLI\Commands;


use tad\WPCLI\Exceptions\BadArgumentException;
use tad\WPCLI\Exceptions\FileCreationException;
use tad\WPCLI\Templates\FileTemplates;
use tad\WPCLI\Utils\JsonFileHandler;

class PluginTests extends \WP_CLI_Command {

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
	protected $dryRun = false;

	/**
	 * @var string
	 */
	protected $dir;

	/**
	 * @var FileTemplates
	 */
	protected $fileTemplates;

	/**
	 * @var JsonFileHandler
	 */
	protected $jsonFileHandler;


	public function __construct( FileTemplates $fileTemplates = null, JsonFileHandler $jsonFileHandler = null ) {
		$this->fileTemplates   = $fileTemplates ?: new FileTemplates();
		$this->jsonFileHandler = $jsonFileHandler ?: new JsonFileHandler();
	}

	/**
	 * @param array $args
	 * @param array $assocArgs
	 *
	 * @throws BadArgumentException
	 */
	public function scaffold( array $args, array $assocArgs ) {
		$this->args      = $args;
		$this->assocArgs = $assocArgs;

		$this->dryRun = $this->getFlagArg( 'dry-run' );
		$this->dir    = $this->getAssocArg( 'dir' );

		if ( false === $this->dir ) {
			$this->dir = getcwd();
			\WP_CLI::line( 'Plugin tests will be scaffolded in the current working folder.' );
		} else {
			if ( ! is_dir( $this->dir ) ) {
				throw new BadArgumentException(
					"Destination folder '{$this->dir}' is not accessible or does not exist."
				);
			}

			\WP_CLI::line( "Plugin tests will be scaffolded in the specified folder '{$this->dir}'" );
		}

		$this->dir = rtrim( $this->dir, '/' );

		if ( $this->dryRun ) {
			return;
		}

		$composerJsonFile = realpath( $this->dir ) . '/composer.json';

		if ( file_exists( $composerJsonFile ) ) {
			\WP_CLI::log( "Existing 'composer.json' file will be updated." );
			$wrote = $this->jsonFileHandler->setFile( $composerJsonFile )->addPropertyValue(
					'require-dev', 'lucatume/wp-browser', '*'
				)->write();

			if ( ! $wrote ) {
				throw new FileCreationException( "Could not update existing 'composer.json' file." );
			}

			\WP_CLI::success( "Existing 'composer.json' file was updated." );
		} else {
			\WP_CLI::log( "Creating '{$composerJsonFile}' file" );
			$created = file_put_contents(
				$composerJsonFile, $this->fileTemplates->getComposerPluginConfig( $assocArgs )
			);
			if ( false === $created ) {
				throw new FileCreationException( "File '{$composerJsonFile}' creation failed" );
			}
			\WP_CLI::success( "New composer.json file created in '{$this->dir}'" );
		}
	}

	/**
	 * @param string $name
	 * @param mixed  $compare
	 */
	protected function getFlagArg( $name ) {
		return ! empty( $this->assocArgs[ $name ] ) ? true : false;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|bool
	 */
	private function getAssocArg( $name ) {
		return ! empty( $this->assocArgs[ $name ] ) ? $this->assocArgs[ $name ] : false;
	}
}