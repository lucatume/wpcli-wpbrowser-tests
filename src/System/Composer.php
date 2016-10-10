<?php

namespace tad\WPCLI\System;


use tad\WPCLI\Exceptions\MissingRequirementException;

class Composer {

	public function ensureComposer( array $assocArgs ) {
		if ( empty( $assocArgs['composer'] ) ) {
			$this->ensureGlobalComposer();

			return 'composer';
		}

		$this->ensureComposerPath( $assocArgs );

		return $assocArgs['composer'];
	}

	protected function ensureGlobalComposer() {
		exec( 'composer --version', $output, $return );
		if ( $return !== 0 ) {
			throw new MissingRequirementException( implode( "\n", array(
				"'composer' (https://getcomposer.org/) command not found or not good.",
				'Either install Composer globally and make it available on your path (https://getcomposer.org/download/)',
				'or specify its path using the --composer option.'
			) ) );
		}
	}

	protected function ensureComposerPath( array $assocArgs ) {
		exec( $assocArgs['composer'] . ' --version', $output, $return );
		if ( $return !== 0 ) {
			throw new MissingRequirementException( implode( "\n", array(
				"specified Composer path '{$assocArgs['composer']}' is not a valid Composer executable.",
				'Either install Composer globally and make it available on your path (https://getcomposer.org/download/)',
				'or specify its path using the --composer option.'
			) ) );
		}
	}
}