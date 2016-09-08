@cleanTemp
Feature: Test that a composer configuration file is updates in the destination folder

  Background:
    Given a WP install

  Scenario: if a Composer configuration file is found in the destination folder it will be updated
    Given the next command is called with the `--dir` parameter
    Given the value of the parameter is `/temp` from data
    Given the `/temp` data folder contains the `composer.json` file with contents:
    """
    {
          "name": "acme/my-plugin",
          "description": "Acme plugin",
          "type": "wordpress-plugin",
          "license": "GPL-3.0+",
          "authors": [
              {
                  "name": "Me",
                  "email": "me@example.com"
              }
          ]
      }
    """
    When I run `wp wpb-scaffold plugin-tests`
    Then STDOUT should contain:
      """
      Existing 'composer.json' file will be updated.
      """
