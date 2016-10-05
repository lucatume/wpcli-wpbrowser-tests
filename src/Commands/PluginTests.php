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

		$targetDir = $this->getScaffoldTargetDir( $args, $assocArgs );

		$this->setTargetDir( $targetDir );

		if ( $this->dryRun ) {
			return;
		}

		$this->scaffoldOrUpdateComposerFile( $assocArgs );

		$shouldGoOn = $this->promptForComposerUpdate();

		if ( ! $shouldGoOn ) {
			$this->end();
		}
	}

	/**
	 * @param array $args
	 * @param array $assocArgs
	 *
	 * @return array
	 * @throws MissingRequiredArgumentException
	 */
	protected function getScaffoldTargetDir( array $args, array $assocArgs ) {
		if ( empty( $assocArgs['dir'] ) && empty( $args[1] ) ) {
			throw new MissingRequiredArgumentException( 'Specify an existing plugin folder basename or a destination using the --dir parameter.' );
		} elseif ( ! empty( $assocArgs['dir'] ) ) {
			$targetDir = $assocArgs['dir'];
			$this->ensureDir( $targetDir );
			\WP_CLI::line( "Scaffolding plugin tests in the folder '{$assocArgs['dir']}'" );
		} else {
			$targetDir = WP_PLUGIN_DIR . '/' . $args[1];
			$this->ensurePluginDir( $targetDir );
			\WP_CLI::line( "Scaffolding plugin tests in '{$args[1]}' folder" );
		}

		return $targetDir;
	}

	/**
	 * @param $candidate
	 */
	protected function setTargetDir( $candidate ) {
		$this->dir = $candidate;
		$this->dir = rtrim( $this->dir, '/' );
	}

	/**
	 * @param array $assocArgs
	 *
	 * @throws FileCreationException
	 */
	protected function scaffoldOrUpdateComposerFile( array $assocArgs ) {
		$composerJsonFile = $this->getComposerFilePath();

		if ( file_exists( $composerJsonFile ) ) {
			$this->updateComposerFile( $composerJsonFile );
		} else {
			$this->createComposerFile( $assocArgs, $composerJsonFile );
		}
	}

	private function promptForComposerUpdate() {
		return \cli\confirm( 'Do you want to update Composer dependencies now', true );
	}

	private function end() {
		\cli\line( "\n\nAll done!" );

		return 0;
	}

	protected function ensureDir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			throw new BadArgumentException( "Invalid destination folder '{$dir}' specified." );
		}
	}

	protected function ensurePluginDir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			throw new BadArgumentException( "Invalid plugin slug specified." );
		}
	}

	/**
	 * @return string
	 */
	protected function getComposerFilePath() {
		$composerJsonFile = realpath( $this->dir ) . '/composer.json';

		return $composerJsonFile;
	}

	/**
	 * @param $composerJsonFile
	 *
	 * @throws FileCreationException
	 */
	protected function updateComposerFile( $composerJsonFile ) {
		\WP_CLI::log( "Existing 'composer.json' file will be updated." );
		$wrote = $this->jsonFileHandler->setFile( $composerJsonFile )->addPropertyValue(
			'require-dev', 'lucatume/wp-browser', '*'
		)->write();

		if ( ! $wrote ) {
			throw new FileCreationException( "Could not update existing 'composer.json' file." );
		}

		\WP_CLI::success( "Existing 'composer.json' file was updated." );
	}

	/**
	 * @param array $assocArgs
	 * @param       $composerJsonFile
	 *
	 * @throws FileCreationException
	 */
	protected function createComposerFile( array $assocArgs, $composerJsonFile ) {
		\WP_CLI::log( "Creating '{$composerJsonFile}' file" );
		$composerFileArgs = array_merge( $this->readPluginInformation(), $assocArgs );
		$created          = file_put_contents(
			$composerJsonFile, $this->fileTemplates->getComposerPluginConfig( $composerFileArgs )
		);
		if ( false === $created ) {
			throw new FileCreationException( "File '{$composerJsonFile}' creation failed" );
		}
		\WP_CLI::success( "New composer.json file created in '{$this->dir}'" );
	}

	protected function readPluginInformation() {
		$pluginData = $this->getPluginData();
		if ( empty( $pluginData ) ) {
			return array();
		}

		$info = array();

		if ( ! ( empty( $pluginData['AuthorName'] ) && empty( $pluginData['Author'] ) ) ) {
			$authorName   = ! empty( $pluginData['AuthorName'] ) ? $pluginData['AuthorName'] : $pluginData['Author'];
			$authorSlug   = sanitize_title( $authorName );
			$pluginSlug   = basename( $this->dir );
			$info['slug'] = "{$authorSlug}/{$pluginSlug}";
			$info['name'] = $authorName;

			if ( ! empty( $pluginData['AuthorURI'] ) ) {
				$domain = parse_url( $pluginData['AuthorURI'], PHP_URL_HOST );
				if ( ! empty( $domain ) ) {
					$info['email'] = str_replace( ' ', '.', strtolower( $authorName ) ) . '@' . $domain;
				}
			}
		}

		if ( ! empty( $pluginData['Description'] ) ) {
			$info['description'] = $pluginData['Description'];
		}

		return $info;
	}

	protected function getPluginData() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( glob( $this->dir . '/*.php' ) as $file ) {
			if ( is_dir( $file ) ) {
				continue;
			}

			if ( substr( $file, - 4 ) == '.php' ) {
				$pluginData = get_plugin_data( $file, false, false );
				if ( false !== $pluginData ) {
					return $pluginData;
				}
			}
		}

		return false;
	}
}
