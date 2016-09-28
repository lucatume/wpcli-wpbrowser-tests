Feature: Test that the plugin tests bootstrap command will read the target plugin data to fill the composer.json file
  informatio

  Background:
    Given a WP install

  @current
  Scenario: if not passed meta information the command will read the information from the plugin header
    When I run `wp scaffold plugin some-plugin --plugin_name="Some Plugin" --plugin_description="Description of the plugin." --plugin_author="Your Name" --plugin_author_uri="http://example.com"`
    When I run `wp wpb-scaffold plugin-tests some-plugin`
    Then the file 'composer.json' in the 'some-plugin' plugin should contain:
        """
        {
            "name": "your-name/some-plugin",
            "description": "Description of the plugin.",
            "type": "wordpress-plugin",
            "minimum-stability": "stable",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "Your Name",
                    "email": "your.name@example.com"
                }
            ],
            "require": {}
        }
        """

#  Scenario: if passed meta information the command will use that in place of the information in the plugin header

#  Scenario: if passed meta incomplete meta information the command will merge that with the information in the plugin header
