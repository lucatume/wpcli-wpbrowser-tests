<?php

namespace tad\WPCLI\Commands;


class Scaffold extends \WP_CLI_Command {

	public function help() {
		\WP_CLI::line( 'usage: wp wpb-scaffold plugin-tests' );
		\WP_CLI::line( '   or: wp wpb-scaffold theme-tests' );
	}
}