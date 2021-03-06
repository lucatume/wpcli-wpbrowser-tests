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
		return implode( DIRECTORY_SEPARATOR, array( WP_CONTENT_DIR, 'themes', $args[1] ) );
	}

	protected function readComponentInformation() {
		if ( ! is_dir( WP_CONTENT_DIR . '/themes/' . basename( $this->dir ) ) ) {
			return array();
		}

		$theme = wp_get_theme( $this->stylesheet );

		if ( ! $theme->exists() ) {
			return array();
		}

		$info = array();


		$themeAuthor = $theme->get( 'Author' );
		if ( ! ( empty( $themeAuthor ) ) ) {
			$authorName   = $themeAuthor;
			$authorSlug   = sanitize_title( $authorName );
			$themeSlug    = basename( $this->dir );
			$info['slug'] = "{$authorSlug}/{$themeSlug}";
			$info['name'] = $authorName;

			$authorUri = $theme->get( 'AuthorURI' );
			if ( ! empty( $authorUri ) ) {
				$domain = parse_url( $authorUri, PHP_URL_HOST );
				if ( ! empty( $domain ) ) {
					$info['email'] = str_replace( ' ', '.', strtolower( $authorName ) ) . '@' . $domain;
				}
			}
		}

		$description = $theme->get( 'Description' );
		if ( ! empty( $description ) ) {
			$info['description'] = $description;
		} else {
			$info['description'] = '';
		}

		return $info;
	}

	protected function init() {
		$this->stylesheet = $this->args[1];
	}

	protected function getWpceptArguments() {
		$arguments = array( 'type' => 'theme', 'theme' => $this->stylesheet );

		return $arguments;
	}
}