Feature: Test that the command will optionally launch Composer and wpcept after the scaffold

  Background:
    Given a WP install

  Scenario: the command will end if the user wants to manually install composer dependencies
    Given I will answer 'n' to the 'composer update' question
    When I run `wp scaffold plugin some-plugin --plugin_name="Some Plugin" --plugin_description="Description of the plugin." --plugin_author="Your Name" --plugin_author_uri="http://example.com"`
    And I run `wp wpb-scaffold plugin-tests some-plugin` with input
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should contain:
    """
    Run `composer install
    """
    Then STDOUT should contain:
    """
    --interactive-mode` to start wp-browser interactive tests setup
    """

  Scenario: the command will end if the user wants to manually update composer dependencies
    Given I will answer 'n' to the 'composer update' question
    Given I run `composer init --name=lucatume/some --description=Some --author="Luca Tumedei <luca@theaveragedev.com>" -n` in the 'some-plugin' plugin
    When I run `wp scaffold plugin some-plugin --plugin_name="Some Plugin" --plugin_description="Description of the plugin." --plugin_author="Your Name" --plugin_author_uri="http://example.com"`
    And I run `wp wpb-scaffold plugin-tests some-plugin` with input
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should contain:
    """
    Run `composer update
    """
    Then STDOUT should contain:
    """
    --interactive-mode` to start wp-browser interactive tests setup
    """

  @pathEnv
  Scenario: the command will launch composer update if the user wants it to
    Given I will answer 'y' to the 'composer update' question
    Given I will answer 'n' to the 'wpcept bootstrap' question
    When I run `wp scaffold plugin some-plugin --plugin_name="Some Plugin" --plugin_description="Description of the plugin." --plugin_author="Your Name" --plugin_author_uri="http://example.com"`
    And I run `wp wpb-scaffold plugin-tests some-plugin` with input
    Then 'composer' should have been called
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should contain:
    """
    --interactive-mode` to start wp-browser interactive tests setup
    """

  @pathEnv @mockWpcept @current
  Scenario: the command will launch wpcept interactive mode if the user wants it to
    Given I will answer 'y' to the 'composer update' question
    Given I will answer 'y' to the 'wpcept bootstrap' question
    Given I'm working on the 'some-plugin' plugin
    When I run `wp scaffold plugin some-plugin --plugin_name="Some Plugin" --plugin_description="Description of the plugin." --plugin_author="Your Name" --plugin_author_uri="http://example.com"`
    And I run `wp wpb-scaffold plugin-tests some-plugin` with input
    Then 'wpcept' should have been called with '--type=plugin'
    Then 'wpcept' should have been called with '--plugins=some-plugin/some-plugin.php'
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should not contain:
    """
    ./vendor/bin/wpcept bootstrap --interactive-mode
    """
