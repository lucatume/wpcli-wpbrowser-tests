<?php

namespace tad\WPCLI\Commands;


class ThemeTests extends TestScaffold {

	protected $scaffoldType = 'theme';

	/**
	 * @var string The current theme stylesheet
	 */
	protected $stylesheet;

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	protected function getDefaultTargetDir( array $args ) {
		return implode( DIRECTORY_SEPARATOR, array( WP_CONTENT_DIR, '/themes/', $args[1] ) );
	}

	protected function readComponentInformation() {
		$theme = wp_get_theme( $this->stylesheet );

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
		$this->stylesheet = $args[1];
	}
}