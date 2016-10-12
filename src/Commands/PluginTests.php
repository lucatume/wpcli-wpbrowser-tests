<?php

namespace tad\WPCLI\Commands;


use tad\WPCLI\Exceptions\BadArgumentException;
use tad\WPCLI\Exceptions\FileCreationException;
use tad\WPCLI\Exceptions\MissingRequiredArgumentException;
use tad\WPCLI\System\Composer;
use tad\WPCLI\Templates\FileTemplates;
use tad\WPCLI\Utils\JsonFileHandler;
use WP_CLI as cli;

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

	/**
	 * @var Composer
	 */
	protected $composer;

	/**
	 * @var bool Whether the `composer.json` file was updated or created.
	 */
	protected $composerFileUpdatedOrCreated;

	/**
	 * @var bool Whether the command should prompt the user to to install after the scaffold or not.
	 */
	protected $noInstall;


	public function __construct(
		FileTemplates $fileTemplates = null,
		JsonFileHandler $jsonFileHandler = null,
		Composer $composer = null
	) {
		$this->fileTemplates   = $fileTemplates ?: new FileTemplates();
		$this->jsonFileHandler = $jsonFileHandler ?: new JsonFileHandler();
		$this->composer = $composer ?: new Composer();
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
		$this->dryRun    = isset( $assocArgs['dry-run'] );
		$this->noInstall = isset( $assocArgs['install'] ) && $assocArgs['install'] == false;

		$targetDir = $this->getScaffoldTargetDir( $args, $assocArgs );

		$this->setTargetDir( $targetDir );

		if ( $this->dryRun ) {
			return 0;
		}

		$this->scaffoldOrUpdateComposerFile( $assocArgs );

		if ( $this->noInstall ) {
			$this->printComposerInstructions();
			$this->printWpceptInstructions();
			$this->end();

			return 0;
		}

		return $this->runInteractiveMode( $assocArgs );
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
			cli::line( "Scaffolding plugin tests in the folder '{$assocArgs['dir']}'" );
		} else {
			$targetDir = WP_PLUGIN_DIR . '/' . $args[1];
			$this->ensurePluginDir( $targetDir );
			cli::line( "Scaffolding plugin tests in '{$args[1]}' folder" );
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
			$this->composerFileUpdatedOrCreated = true;
		} else {
			$this->createComposerFile( $assocArgs, $composerJsonFile );
			$this->composerFileUpdatedOrCreated = false;
		}
	}

	protected function printComposerInstructions() {
		$updateMessage  = 'Run `composer update` from this folder to install or update wp-browser';
		$installMessage = 'Run `composer install` from this folder to install wp-browser';
		$message        = $this->composerFileUpdatedOrCreated ? $updateMessage : $installMessage;
		cli::line( $message . "\n" );
	}

	protected function printWpceptInstructions() {
		cli::line( "Run `./vendor/bin/wpcept bootstrap --interactive-mode` to start wp-browser interactive test setup\n" );
	}

	private function end( $status = 0 ) {
		if ( $status === 0 ) {
			\cli\line( "\n\nAll done!" );
		} else {
			\cli\line( "\n\nSomething went wrong... Read the output above to debug." );
		}

		return $status;
	}

	/**
	 * @param array $assocArgs
	 *
	 * @return int
	 */
	protected function runInteractiveMode( array $assocArgs ) {
		if ( ! ( $this->promptForComposerUpdate() ) ) {
			$this->printComposerInstructions();
			$this->printWpceptInstructions();
			$this->end();

			return 0;
		}

		$this->runComposerAction( $assocArgs );

		if ( ! ( $this->promptForWpceptBootstrap() ) ) {
			$this->printWpceptInstructions();
			$this->end();

			return 0;
		}

		$wpceptExitStatus = $this->runWpcept();

		$this->end( $wpceptExitStatus );

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
		cli::log( "Existing 'composer.json' file will be updated." );
		$wrote = $this->jsonFileHandler->setFile( $composerJsonFile )
		                               ->addPropertyValue( 'require-dev', 'lucatume/wp-browser', '*' )
		                               ->write();

		if ( ! $wrote ) {
			throw new FileCreationException( "Could not update existing 'composer.json' file." );
		}

		cli::success( "Existing 'composer.json' file was updated." );
	}

	/**
	 * @param array $assocArgs
	 * @param       $composerJsonFile
	 *
	 * @throws FileCreationException
	 */
	protected function createComposerFile( array $assocArgs, $composerJsonFile ) {
		cli::log( "Creating '{$composerJsonFile}' file" );
		$composerFileArgs = array_merge( $this->readPluginInformation(), $assocArgs );
		$created          = file_put_contents( $composerJsonFile,
			$this->fileTemplates->getComposerPluginConfig( $composerFileArgs ) );
		if ( false === $created ) {
			throw new FileCreationException( "File '{$composerJsonFile}' creation failed" );
		}
		cli::success( "New composer.json file created in '{$this->dir}'" );
	}

	private function promptForComposerUpdate() {
		$action = $this->composerFileUpdatedOrCreated ? 'update' : 'install';

		return \cli\confirm( 'Do you want to ' . $action . ' Composer dependencies now', true );
	}

	/**
	 * @param array $assocArgs
	 */
	protected function runComposerAction( array $assocArgs ) {
		$action          = $this->composerFileUpdatedOrCreated ? 'update' : 'install';
		$composerCommand = sprintf( '%s %s -d=%s', $assocArgs['composer'], $action, $this->dir );
		$env             = array( 'PATH' => getenv( 'PATH' ) );
		$composerProcess = cli\Process::create( $composerCommand, $this->dir, $env );
		/** @var cli\ProcessRun $composerProcessRunStatus */
		$composerProcessRunStatus = $composerProcess->run();

		/** @noinspection PhpUndefinedFieldInspection */
		if ( $composerProcessRunStatus->return_code || ! empty( $composerProcessRunStatus->stderr ) ) {
			$this->outputSubProcessError( $composerCommand, $composerProcessRunStatus );

			return 1;
		}

		$this->outputSubProcessOutput( $composerProcessRunStatus );

		return 0;
	}

	protected function promptForWpceptBootstrap() {
		return \cli\confirm( "\nDo you want to run wp-browser interactive bootstrap now", true );
	}

	protected function runWpcept() {
		$wpcept = $this->isWindows() ? 'wpcept.bat' : 'wpcept';

		// if we are following through then we should control the path to the `vendor/bin` folder
		$expectedLocation = $this->dir . '/vendor/bin/' . $wpcept;

		if ( ! file_exists( $expectedLocation ) ) {
			\cli\err( "WPBrowser bin 'wpcept' not found in the expected location '{$expectedLocation}'.\nRun it manually using the 'wpcept bootstrap --interactive' command." );

			return - 1;
		}

		chdir( $this->dir );

		/** @noinspection PhpUndefinedVariableInspection */
		$arguments = [
			'--dbHost=' . DB_HOST,
			'--dbName=' . DB_NAME,
			'--dbUser=' . DB_USER,
			'--dbPassword=' . DB_PASSWORD,
			'--tablePrefix=' . $table_prefix,
			'--url=' . home_url(),
			'--wpRootFolder=' . ABSPATH,
			'--adminPath=' . ABSPATH . '/wp-admin',
			'--plugins=' . basename( $this->dir )
		];

		$wpceptCommand = './vendor/bin/' . $wpcept . ' bootstrap --interactive' . ' ' . implode( ' ', $arguments );

		passthru( $wpceptCommand, $return );

		return $return;
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

	/**
	 * @param string         $subprocessCommand
	 * @param cli\ProcessRun $subProcessRunStatus
	 */
	protected function outputSubProcessError( $subprocessCommand, $subProcessRunStatus ) {
		cli::error_multi_line( "Error while running '{$subprocessCommand}':'n\n" . $subProcessRunStatus );
	}

	/**
	 * @param cli\ProcessRun $composerProcessRunStatus
	 */
	protected function outputSubProcessOutput( $composerProcessRunStatus ) {
		/** @noinspection PhpUndefinedFieldInspection */
		echo $composerProcessRunStatus->stdout;
	}

	private function isWindows() {
		if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
			return true;
		}

		return false;
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

	/**
	 * @param array $assocArgs
	 *
	 * @return int
	 */
	protected function runNonInteractiveMode( array $assocArgs ) {
		$this->runComposerAction( $assocArgs );
		$wpceptExitStatus = $this->runWpcept();

		$this->end( $wpceptExitStatus );

		return 0;
	}
}
