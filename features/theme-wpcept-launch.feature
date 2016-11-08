Feature: Test that the command will optionally launch Composer and wpcept after the scaffold

  Background:
    Given a WP install

  Scenario: the command will end if the user wants to manually install composer dependencies
    Given I will answer 'n' to the 'composer update' question
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    And I run `wp wpb-scaffold theme-tests some-theme` with input
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
    Given I run `composer init --name=lucatume/some --description=Some --author="Luca Tumedei <luca@theaveragedev.com>" -n` in the 'some-theme' theme
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    And I run `wp wpb-scaffold theme-tests some-theme` with input
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
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    And I run `wp wpb-scaffold theme-tests some-theme` with input
    Then 'composer' should have been called
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should contain:
    """
    --interactive-mode` to start wp-browser interactive tests setup
    """

  @pathEnv @mockWpcept
  Scenario: the command will launch wpcept interactive mode if the user wants it to
    Given I will answer 'y' to the 'composer update' question
    Given I will answer 'y' to the 'wpcept bootstrap' question
    Given I'm working on the 'some-theme' theme
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    And I run `wp wpb-scaffold theme-tests some-theme` with input
    Then 'wpcept' should have been called
    Then STDOUT should contain:
    """
    All done
    """
    Then STDOUT should not contain:
    """
    ./vendor/bin/wpcept bootstrap --interactive-mode
    """
