Feature: Test that a composer configuration file is created or in the destination folder

  Background:
    Given a WP install

  @cleanTemp
  Scenario: if not found a composer configuration file is created
    Given the next command is called with the `--dir` parameter
    Given the value of the parameter is `/temp` from data
    When I run `wp wpb-scaffold plugin-tests`
    Then the file `composer.json` should exist in the `/temp` folder in data
    Then STDOUT should contain:
      """
      New composer.json file created in
      """

  @cleanTemp
  Scenario: created composer configuration file should use default values
    Given the next command is called with the `--dir` parameter
    Given the value of the parameter is `/temp` from data
    When I run `wp wpb-scaffold plugin-tests`
    Then the file `composer.json` should exist in the `/temp` folder in data
    Then the json file `composer.json` in the `/temp` data folder should contain:
        """
        {
            "name": "acme/my-plugin",
            "description": "My plugin",
            "type": "wordpress-plugin",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "Me",
                    "email": "me@example.com"
                }
            ],
            "require": {}
        }
        """

  @cleanTemp
  Scenario: created composer configuration file should contain specified values
    Given the next command is called with the `--dir` parameter
    Given the value of the parameter is `/temp` from data
    Given the next command is called with the `--slug` parameter
    Given the value of the parameter is `lucatume/a-plugin`
    Given the next command is called with the `--description` parameter
    Given the value of the parameter is `Just a plugin`
    Given the next command is called with the `--name` parameter
    Given the value of the parameter is `Luca Tumedei`
    Given the next command is called with the `--email` parameter
    Given the value of the parameter is `luca@theaveragedev.com`
    When I run `wp wpb-scaffold plugin-tests`
    Then the file `composer.json` should exist in the `/temp` folder in data
    Then the json file `composer.json` in the `/temp` data folder should contain:
        """
        {
            "name": "lucatume/a-plugin",
            "description": "Just a plugin",
            "type": "wordpress-plugin",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "Luca Tumedei",
                    "email": "luca@theaveragedev.com"
                }
            ],
            "require": {}
        }
        """
    @cleanTemp
    Scenario: it allows the user to override certain values only
      Given the next command is called with the `--dir` parameter
      Given the value of the parameter is `/temp` from data
      Given the next command is called with the `--description` parameter
      Given the value of the parameter is `Just a plugin`
      Given the next command is called with the `--name` parameter
      Given the value of the parameter is `Luca Tumedei`
      Given the next command is called with the `--email` parameter
      Given the value of the parameter is `luca@theaveragedev.com`
      When I run `wp wpb-scaffold plugin-tests`
      Then the file `composer.json` should exist in the `/temp` folder in data
      Then the json file `composer.json` in the `/temp` data folder should contain:
        """
        {
            "name": "acme/my-plugin",
            "description": "Just a plugin",
            "type": "wordpress-plugin",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "Luca Tumedei",
                    "email": "luca@theaveragedev.com"
                }
            ],
            "require": {}
        }
        """
