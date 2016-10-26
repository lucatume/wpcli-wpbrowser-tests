<?php
namespace tad\WPCLI\Utils;

/**
 * Class SubProcess
 *
 * A copy of the WP_CLI\Process class to implement a process runner that would allow for
 * input and output access.
 *
 * @package tad\WPCLI\Utils
 */
class SubProcess {

	/**
	 * SubProcess constructor.
	 *
	 * @param string $command
	 * @param string $cwd
	 * @param array  $env
	 */
	public function __construct( $command, $cwd = null, $env = array() ) {
		$this->command = $command;
		$this->cwd     = $cwd;
		$this->env     = $env;
	}

	/**
	 * Run the command giving it access to input and output.
	 *
	 * @return \stdClass An exit status value object
	 */
	public function run() {
		$cwd = $this->cwd;

		$descriptors = array(
			0 => STDIN,
			1 => STDOUT,
			2 => array( 'pipe', 'w' ),
		);

		$subProcess = proc_open( $this->command, $descriptors, $pipes, $cwd, $this->env );

		$stderr = stream_get_contents( $pipes[2] );
		fclose( $pipes[2] );

		return (object) array(
			'stderr'      => $stderr,
			'return_code' => proc_close( $subProcess ),
			'command'     => $this->command,
			'cwd'         => $cwd,
			'env'         => $this->env
		);
	}
}
