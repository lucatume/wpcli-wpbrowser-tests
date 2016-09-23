Feature: Test that the plugin tests bootstrap command will read the target plugin data to fill the composer.json file
  informatio

  Background:
    Given a WP install

    Scenario: if not passed meta information the command will read the information from the plugin header

  Scenario: if passed meta information the command will use that in place of the information in the plugin header

  Scenario: if passed meta incomplete meta information the command will merge that with the information in the plugin header
