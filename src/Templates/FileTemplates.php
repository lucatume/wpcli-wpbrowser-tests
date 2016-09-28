<?php

namespace tad\WPCLI\Templates;


class FileTemplates {

	public function getComposerPluginConfig( array $data ) {
		$template = <<<JSON
{
    "name": "{{slug}}",
    "description": "{{description}}",
    "type": "wordpress-plugin",
    "minimum-stability": "stable",
    "require-dev": {
        "lucatume/wp-browser": "*"
    },
    "license": "GPL-3.0+",
    "authors": [
        {
            "name": "{{name}}",
            "email": "{{email}}"
        }
    ],
    "require": {}
}
JSON;

		$defaults = array(
			'slug'  => 'acme/my-plugin',
			'description' => 'My plugin',
			'name'  => 'Me',
			'email' => 'me@example.com'
		);

		return $this->compile( $template, array_merge( $defaults, $data ) );
	}

	/**
	 * @param array  $data
	 * @param string $template
	 */
	protected function compile( $template, array $data ) {
		foreach ( $data as $key => $value ) {
			if ( false !== strpos( $template, $key ) ) {
				$template = str_replace( '{{' . $key . '}}', $value, $template );
			}
		}

		return $template;
	}
}