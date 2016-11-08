@cleanTemp
Feature: Test that a composer configuration file is updates in the destination folder

  Background:
    Given a WP install

  Scenario: if a Composer configuration file is found in the destination folder it will be updated
    Given the next command is called with the `--no-install` option
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

  Scenario: if a Composer configuration file is found then the file will be updated
    Given the next command is called with the `--no-install` option
    Given the next command is called with the `--dir` parameter
    Given the value of the parameter is `/temp` from data
    Given the `/temp` data folder contains the `composer.json` file with contents:
      """
      {
          "name": "lucatume/yet-another-plugin",
          "description": "Yet another plugin",
          "type": "wordpress-plugin",
          "license": "GPL 2.0",
          "authors": [
              {
                  "name": "Luca Tumedei",
                  "email": "luca@theaveragedev.com"
              }
          ],
          "minimum-stability": "stable",
          "require": {
              "some/requirement-1": "~1.3",
              "some/requirement-2": "^3.1"
          },
          "scripts": {
              "post-install-cmd": [
                  "xrstf\\Composer52\\Generator::onPostInstallCmd"
              ],
              "post-update-cmd": [
                "xrstf\\Composer52\\Generator::onPostInstallCmd"
              ],
              "post-autoload-dump": [
                "xrstf\\Composer52\\Generator::onPostInstallCmd"
              ]
          },
          "require-dev": {
              "acme/dev-requirement-1": "*",
              "acme/dev-requirement-2": "^1.0",
              "acme/dev-requirement-3": "*",
              "acme/dev-requirement-4": "~3.0"
          },
          "autoload": {
              "psr-0": {
                  "yap_": "src/"
              }
          }
      }
      """
    When I run `wp wpb-scaffold plugin-tests`
    Then the json file `composer.json` in the `/temp` data folder should contain:
      """
      {
          "name": "lucatume/yet-another-plugin",
          "description": "Yet another plugin",
          "type": "wordpress-plugin",
          "license": "GPL 2.0",
          "authors": [
              {
                  "name": "Luca Tumedei",
                  "email": "luca@theaveragedev.com"
              }
          ],
          "minimum-stability": "stable",
          "require": {
              "some/requirement-1": "~1.3",
              "some/requirement-2": "^3.1"
          },
          "scripts": {
              "post-install-cmd": [
                  "xrstf\\Composer52\\Generator::onPostInstallCmd"
              ],
              "post-update-cmd": [
                  "xrstf\\Composer52\\Generator::onPostInstallCmd"
              ],
              "post-autoload-dump": [
                  "xrstf\\Composer52\\Generator::onPostInstallCmd"
              ]
          },
          "require-dev": {
              "acme/dev-requirement-1": "*",
              "acme/dev-requirement-2": "^1.0",
              "acme/dev-requirement-3": "*",
              "acme/dev-requirement-4": "~3.0",
              "lucatume/wp-browser": "*"
          },
          "autoload": {
              "psr-0": {
                  "yap_": "src/"
              }
          }
      }
      """
