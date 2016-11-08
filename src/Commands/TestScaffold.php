<?php

namespace tad\WPCLI\Commands;


use tad\WPCLI\Exceptions\BadArgumentException;
use tad\WPCLI\Exceptions\FileCreationException;
use tad\WPCLI\Exceptions\MissingRequiredArgumentException;
use tad\WPCLI\System\Composer;
use tad\WPCLI\Templates\FileTemplates;
use tad\WPCLI\Utils\JsonFileHandler;
use tad\WPCLI\Utils\SubProcess;
use WP_CLI as cli;

abstract class TestScaffold extends \WP_CLI_Command {

	protected $scaffoldType = 'plugin';

	/**
	 * @var bool
	 */
	protected $dryRun = false;
	/**
	 * @var bool
	 */
	protected $debug = false;
	/**
	 * @var JsonFileHandler
	 */
	protected $jsonFileHandler;
	/**
	 * @var Composer
	 */
	protected $composer;
	/**
	 * @var The main plugin file.
	 */
	protected $pluginFile;
	/**
	 * @var array
	 */
	protected $componentInformation = array();
	/**
	 * @var bool
	 */
	protected $skipComposerUpdate = false;
	/**
	 * @var FileTemplates
	 */
	protected $fileTemplates;
	/**
	 * @var bool Whether the command should prompt the user to to install after the scaffold or not.
	 */
	protected $noInstall;

	/**
	 * @var array
	 */
	protected $args;

	/**
	 * @var array
	 */
	protected $assocArgs = array();

	/**
	 * @var bool
	 */
	protected $composerFileUpdatedOrCreated = false;

	public function __construct(
		FileTemplates $fileTemplates = null,
		JsonFileHandler $jsonFileHandler = null,
		Composer $composer = null
	) {
		$this->fileTemplates   = $fileTemplates ?: new FileTemplates();
		$this->jsonFileHandler = $jsonFileHandler ?: new JsonFileHandler();
		$this->composer        = $composer ?: new Composer();
	}

	/**
	 * @param array $args
	 * @param array $assocArgs
	 *
	 * @throws BadArgumentException
	 */
	public function scaffold( array $args, array $assocArgs ) {
		$this->args               = $args;
		$this->assocArgs          = $assocArgs;
		$this->dryRun             = isset( $assocArgs['dry-run'] );
		$this->noInstall          = isset( $assocArgs['install'] ) && $assocArgs['install'] == false;
		$this->debug              = isset( $assocArgs['debug'] );
		$this->skipComposerUpdate = isset( $assocArgs['skip-composer-update'] );
		$this->init();

		$targetDir = $this->getScaffoldTargetDir( $args, $assocArgs );

		$this->setTargetDir( $targetDir );

		if ( $this->dryRun ) {
			return 0;
		}

		$this->componentInformation = $this->readComponentInformation();

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
			throw new MissingRequiredArgumentException(
				'Specify an existing ' . $this->scaffoldType . ' folder basename or a destination using the --dir parameter.'
			);
		} elseif ( ! empty( $assocArgs['dir'] ) ) {
			$targetDir = $assocArgs['dir'];
			$this->ensureDir( $targetDir );
			cli::line( 'Scaffolding ' . $this->scaffoldType . " tests in the folder '{$assocArgs['dir']}'" );
		} else {
			$targetDir = $this->getDefaultTargetDir( $args );
			$this->ensureComponentDir( $targetDir );
			cli::line( 'Scaffolding ' . $this->scaffoldType . " tests in '{$args[1]}' folder" );
		}

		return $targetDir;
	}

	/**
	 * @param $candidate
	 */
	protected function setTargetDir( $candidate ) {
		$this->dir = $candidate;
		$this->dir = rtrim( $this->dir, ' / ' );
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
		$dir            = getcwd() === $this->dir ? '' : ' -d ' . $this->dir;
		$updateMessage  = "Run `composer update$dir` to install or update wp-browser";
		$installMessage = "Run `composer install$dir` to install wp-browser";
		$message        = $this->composerFileUpdatedOrCreated ? $updateMessage : $installMessage;
		cli::line( $message );
	}

	protected function printWpceptInstructions() {
		$dir = getcwd() === $this->dir ? '' : 'cd ' . $this->dir . ' && ';
		cli::line( "Run `$dir./vendor/bin/wpcept bootstrap --interactive-mode` to start wp-browser interactive tests setup\n" );
	}

	public function end( $status = 0 ) {
		if ( $status === 0 ) {
			cli::success( "All done!" );
		} else {
			cli::error( "Something went wrong... Read the output above to debug." );
		}

		return $status;
	}

	/**
	 * @param array $assocArgs
	 *
	 * @return int
	 */
	protected function runInteractiveMode( array $assocArgs ) {
		if ( ! $this->skipComposerUpdate ) {
			if ( ! ( $this->promptForComposerUpdate() ) ) {
				cli::line();
				$this->printComposerInstructions();
				$this->printWpceptInstructions();
				$this->end();

				return 0;
			}

			$this->runComposerAction( $assocArgs );
		} else {
			cli::line( "\nComposer update skipped due to `--skip-composer-update` flag." );
		}

		if ( ! ( $this->promptForWpceptBootstrap() ) ) {
			cli::line();
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

	protected function ensureComponentDir( $dir ) {
		if ( ! is_dir( $dir ) ) {
			throw new BadArgumentException( 'Invalid ' . $this->scaffoldType . ' slug specified.' );
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
		$composerFileArgs = array_merge( $this->readComponentInformation(), $assocArgs, array( 'type' => $this->scaffoldType ) );
		$created          = file_put_contents(
			$composerJsonFile, $this->fileTemplates->getComposerPluginConfig( $composerFileArgs )
		);
		if ( false === $created ) {
			throw new FileCreationException( "File '{$composerJsonFile}' creation failed" );
		}
		cli::success( "New composer.json file created in '{$this->dir}'" );
	}

	protected function promptForComposerUpdate() {
		$action = $this->composerFileUpdatedOrCreated ? 'update' : 'install';

		return \cli\confirm( "\nDo you want to " . $action . ' Composer dependencies now', true );
	}

	/**
	 * @param array $assocArgs
	 */
	protected function runComposerAction( array $assocArgs ) {
		$action          = $this->composerFileUpdatedOrCreated ? 'update' : 'install';
		$composerCommand = sprintf( '%s %s --working-dir %s', $assocArgs['composer'], $action, $this->dir );
		$env             = array( 'PATH' => getenv( 'PATH' ), 'HOME' => getenv( 'HOME' ) );
		if ( getenv( 'COMPOSER_HOME' ) ) {
			$env['COMPOSER_HOME'] = getenv( 'COMPOSER_HOME' );
		}
		$composerProcess = cli\Process::create( $composerCommand, $this->dir, $env );

		cli::line( 'Running composer ' . $action . '...' );

		/** @var cli\ProcessRun $composerProcessRunStatus */
		$composerProcessRunStatus = $composerProcess->run_check();

		/** @noinspection PhpUndefinedFieldInspection */
		if ( $composerProcessRunStatus->return_code || ! empty( $composerProcessRunStatus->stderr ) ) {
			$this->outputSubProcessError( $composerCommand, $composerProcessRunStatus );

			return 1;
		}

		cli::line( 'Composer ' . $action . ' complete!' );

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
			\cli\err(
				"WPBrowser bin 'wpcept' not found in the expected location '{$expectedLocation}'.\nRun it manually using the 'wpcept bootstrap --interactive' command."
			);

			return - 1;
		}

		chdir( $this->dir );

		/** @var \wpdb $wpdb */
		global $wpdb;

		/** @noinspection PhpUndefinedVariableInspection */
		$arguments = array(
			'dbHost'       => DB_HOST,
			'dbName'       => DB_NAME,
			'dbUser'       => DB_USER,
			'dbPassword'   => DB_PASSWORD,
			'tablePrefix'  => $wpdb->prefix,
			'url'          => home_url(),
			'wpRootFolder' => ABSPATH,
			'adminPath'    => str_replace( home_url(), '', site_url() ) . '/wp-admin',
		);

		if ( ! empty( $this->pluginFile ) ) {
			$arguments['plugins'] = basename( dirname( $this->pluginFile ) ) . '/' . basename( $this->pluginFile );
		}

		array_walk(
			$arguments, function ( &$value, $key ) {
			$value = '--' . $key . '=' . escapeshellarg( $value );
		}
		);

		$arguments[] = '--interactive';
		$arguments[] = '--ansi';

		$path          = escapeshellarg( $this->dir );
		$wpceptCommand = sprintf( './vendor/bin/%s bootstrap %s %s', $wpcept, $path, implode( ' ', $arguments ) );

		if ( $this->debug ) {
			$wpceptCommand . ' -vvv';
		}

		// Give the sub-process some space
		echo "\n";
		$wpceptProcess = new SubProcess( $wpceptCommand, $this->dir );
		$exit          = $wpceptProcess->run();

		return $exit->return_code;
	}

	/**
	 * @param string         $subprocessCommand
	 * @param cli\ProcessRun $subProcessRunStatus
	 */
	protected function outputSubProcessError( $subprocessCommand, $subProcessRunStatus ) {
		cli::error_multi_line( "Error while running '{$subprocessCommand}':'n\n" . $subProcessRunStatus );
	}

	protected function isWindows() {
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
					$this->pluginFile = $file;

					return $pluginData;
				}
			}
		}

		return false;
	}

	abstract protected function getDefaultTargetDir( array $args );

	abstract protected function readComponentInformation();

	abstract protected function init();
}
