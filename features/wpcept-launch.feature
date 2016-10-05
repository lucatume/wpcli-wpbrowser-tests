Feature: Test that the command will optionally launch Composer and wpcept after the scaffold

  Background:
    Given a WP install

  @current
  Scenario: the command will end if the user wants to manually update composer dependencies
    Given I will answer 'n' to the 'composer update' question
    When I run `wp scaffold plugin some-plugin --plugin_name="Some Plugin" --plugin_description="Description of the plugin." --plugin_author="Your Name" --plugin_author_uri="http://example.com"`
    When I run `wp wpb-scaffold plugin-tests some-plugin` with input
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should contain:
    """
    Run `composer update` to install or update wp-browser
    """
    Then STDOUT should contain:
    """
    Run `./vendor/bin/wpcept bootstrap --interactive-mode` to start wp-browser interactive test setup
    """

#  Scenario: the command will launch composer update if the user wants it to
#
#  Scenario: the command will end if the user wants to manually launch wpcept interactive mode
#
#  Scenario: the command will launch wpcept interactive mode if the user wants it to
