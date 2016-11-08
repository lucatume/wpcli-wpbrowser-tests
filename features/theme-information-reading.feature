@current
Feature: Test that the theme tests bootstrap command will read the target theme data to fill the composer.json file
  information

  Background:
    Given a WP install

  Scenario: if not passed meta information the command will read the information from the theme header
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    When I run `wp wpb-scaffold theme-tests some-theme --no-install`
    Then the file 'composer.json' in the 'some-theme' theme should contain:
        """
        {
            "name": "your-name/some-theme",
            "description": "",
            "type": "wordpress-theme",
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

  Scenario: if passed meta information the command will use that in place of the information in the theme header
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    When I run `wp wpb-scaffold theme-tests some-theme --no-install --slug="someone/some-project" --name="John Doe" --description="Some Doe theme" --email="doe@doe.com"`
    Then the file 'composer.json' in the 'some-theme' theme should contain:
        """
        {
            "name": "someone/some-project",
            "description": "Some Doe theme",
            "type": "wordpress-theme",
            "minimum-stability": "stable",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "John Doe",
                    "email": "doe@doe.com"
                }
            ],
            "require": {}
        }
        """

  Scenario: if passed meta incomplete meta information the command will merge that with the information in the theme header
    When I run `wp scaffold child-theme some-theme --parent_theme=twentysixteen --theme_name="Some Theme" --author="Your Name" --author_uri="http://example.com" --theme_uri="http://example.com/some-theme"`
    When I run `wp wpb-scaffold theme-tests some-theme --no-install --slug="someone/some-project" --name="John Doe"`
    Then the file 'composer.json' in the 'some-theme' theme should contain:
        """
        {
            "name": "someone/some-project",
            "description": "",
            "type": "wordpress-theme",
            "minimum-stability": "stable",
            "require-dev": {
                "lucatume/wp-browser": "*"
            },
            "license": "GPL-3.0+",
            "authors": [
                {
                    "name": "John Doe",
                    "email": "your.name@example.com"
                }
            ],
            "require": {}
        }
        """
