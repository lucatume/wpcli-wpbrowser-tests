<?php

namespace tad\WPCLI\Commands;


use tad\WPCLI\Exceptions\BadArgumentException;
use tad\WPCLI\Exceptions\FileCreationException;
use tad\WPCLI\Exceptions\MissingRequiredArgumentException;
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

		$this->dryRun = isset( $assocArgs['dry-run'] );
		$candidate    = '';

		if ( empty( $assocArgs['dir'] ) && empty( $args[1] ) ) {
			throw new MissingRequiredArgumentException(
				'Specify an existing plugin folder basename or a destination using the --dir parameter.'
			);
		} elseif ( ! empty( $assocArgs['dir'] ) ) {
			$candidate = $assocArgs['dir'];
			$this->ensureDir( $candidate );
			\WP_CLI::line( "Scaffolding plugin tests in the folder '{$assocArgs['dir']}'" );
		} else {
			$candidate = WP_PLUGIN_DIR . '/' . $args[1];
			$this->ensurePluginDir( $candidate );
			\WP_CLI::line( "Scaffolding plugin tests in '{$args[1]}' folder" );
		}

		$this->dir = $candidate;
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

	protected function ensureDir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			throw new BadArgumentException(
				"Invalid destination folder '{$dir}' specified."
			);
		}
	}

	protected function ensurePluginDir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			throw new BadArgumentException(
				"Invalid plugin slug specified."
			);
		}
	}
}