Feature: Test that the plugin tests bootstrap command will read the target plugin data to fill the composer.json file
  informatio

  Background:
    Given a WP install

  Scenario: if not passed meta information the command will read the information from the plugin header
    Given the 'some-plugin' plugin folder exists
    Given the file 'plugin.php' in the 'plugin.php' plugin contains:
        """
        <?php
        /*
        Plugin Name: Some Plugin
        Plugin URI: https://wordpress.org/plugins/some-plugin/
        Description: Just some plugin
        Version: 0.1.0
        Author: Luca Tumedei
        Author URI: http://theaveragedev.com
        Text Domain: some-plugin
        Domain Path: /languages
        */
        """
    When I run `wp wpb-scaffold plugin-tests some-plugin`
    Then the file `composer.json` in the `some-plugin` plugin should contain:
        """
        {
            "name": "luca-tumedei/some-plugin",
            "description": "Some plugin",
            "type": "wordpress-plugin",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "Luca Tumedei",
                    "email": "luca.tumedei@theaveragedev.com"
                }
            ],
            "require": {}
        }
        """

#  Scenario: if passed meta information the command will use that in place of the information in the plugin header

#  Scenario: if passed meta incomplete meta information the command will merge that with the information in the plugin header
