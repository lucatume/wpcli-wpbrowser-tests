<?php

namespace tad\WPCLI\Commands;

class PluginTests extends TestScaffold {

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	protected function getDefaultTargetDir( array $args ) {
		return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $args[1];
	}

	protected function readComponentInformation() {
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

	protected function init() {
		// no op
	}

	protected function getWpceptArguments() {
		$arguments = array( 'type' => 'plugin' );

		if ( ! empty( $this->pluginFile ) ) {
			$arguments['plugins'] = basename( dirname( $this->pluginFile ) ) . '/' . basename( $this->pluginFile );
		}

		return $arguments;
	}
}
