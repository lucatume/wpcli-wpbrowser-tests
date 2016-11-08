lucatume/wpcli-wpbrowser-tests
==============================

Scaffold wp-browser based tests for a plugin or theme

[![Build Status](https://travis-ci.org/lucatume/wpcli-wpbrowser-tests.svg?branch=master)](https://travis-ci.org/lucatume/wpcli-wpbrowser-tests)

Quick links: [Using](#Using) | [Installing](#Installing) | [Contributing](#Contributing)

## Using

The package adds two commands to [wp-cli](http://wp-cli.org/ "Command line interface for WordPress - WP-CLI") to scaffold [wp-browser](https://github.com/lucatume/wp-browser "lucatume/wp-browser · GitHub") based tests for themes and plugins.
The first one to scaffold plugin tests:

```shell
wp wpb-scaffold plugin-tests my-plugin
```

And the second one to scaffold theme tests:

```shell
wp wpb-scaffold theme-tests my-theme
```

Both commands will scaffold or update a [Composer](https://getcomposer.org/) `composer.json` configuration file, launch the `composer update` or `composer install` and then launch [wp-browser](https://github.com/lucatume/wp-browser "lucatume/wp-browser · GitHub") own [interactive mode](https://github.com/lucatume/wp-browser#bootstrap) to complete the test scaffolding process.
Both commands offer a degree of control of customization on the process detailed below.

### Command arguments
`--dry-run` - whether the command should update or create the [Composer](https://getcomposer.org/) file or it should just state what shoudl be done.
`--no-install` - whether the command should run the install operations or not; a step after the `--dry-run` option this will make the command udpate or create the needed Composer `composer.json` file and then exit.
`--skip-composer-update` - whether the `composer update`  operation should run or not before eventually staring wp-browser interactive mode bootstrap operations; users that would not like the plugin or theme dependencies to be update as a side effect of the command should use this flag; the flag will be ignored if there was no previous Composer configuration file in the plugin or theme root folder.
`--composer=<composer path>` - if this option is not set the commands will rely on the system wide `composer` command to run Composer operations; if the `composer` command has not been added to the system path or you would like to use a specific version of Composer use this option like

    wp wpb-scaffold plugin-tests some-plugin --composer="/some/custom/path/composer"

`--dir=<dir>` - allows you to specify the directory root where tests should be scaffolded; by default the commands will use the slug to find the plugin or theme root folder but the option allows you to control the destination even outside of the WordPress installation folder.
An example usage:

    wp wpb-scaffold plugin-tests some-plugin --dir="/user/repos/wordpress/plugins/some-plugin"

`--slug=<slug>` - allows you to specify the slug that should be used in the Composer `composer.json` file Composer configuration file; if not set the command will use the existing plugin or theme information or set a default one. An example usage:

    wp wpb-scaffold plugin-tests some-plugin --slug="acme/my-plugin"

`--description=<description>` - allows you to specify the description that should be used in the Composer `composer.json` file Composer configuration file; if not set the command will use the existing plugin or theme information or set a default one. An example usage:

    wp wpb-scaffold plugin-tests some-plugin --description="My awesome plugin!"

`--name=<name>` - allows you to specify the name of the author that should be used in the Composer `composer.json` file Composer configuration file; if not set the command will use the existing plugin or theme information or set a default one. An example usage:

    wp wpb-scaffold plugin-tests some-plugin --name="John Doe"

`--email=<email>` - allows you to specify the email of the author that should be used in the Composer `composer.json` file Composer configuration file; if not set the command will use the existing plugin or theme information or set a default one. An example usage:

    wp wpb-scaffold plugin-tests some-plugin --email="john@doe.com"

As any [wp-cli](https://wp-cli.org/ "Command line interface for WordPress - WP-CLI") additional command the command will also support any [global option and flat](http://wp-cli.org/commands/).

## Installing

The package is still not available through the official installation channel, for the time being use:

```shell
git clone https://github.com/lucatume/wpcli-wpbrowser-tests.git \
	~/.wp-cli/packages/vendor/lucatume/wpcli-wpbrowser-tests
```

## Contributing

I appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. I encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

### Reporting a bug

Think you’ve found a bug? I’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/lucatume/wpcli-wpbrowser-tests/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/lucatume/wpcli-wpbrowser-tests/issues/new) with the following:

1. What you were doing (e.g. "When I run `wp wpb-scaffold plugin-tests`").
2. What you saw (e.g. "I see a fatal about a class being undefined.").
3. What you expected to see (e.g. "I expected to see the list of posts.")

Include as much detail as you can, and clear steps to reproduce if possible.

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/lucatume/wpcli-wpbrowser-tests/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, please follow our guidelines for creating a pull request to make sure it's a pleasant experience:

1. Create a feature branch for each contribution.
2. Submit your pull request early for feedback.
3. Include functional tests with your changes. [Read the WP-CLI documentation](https://wp-cli.org/docs/pull-requests/#functional-tests) for an introduction.
4. Follow the [WordPress Coding Standards](http://make.wordpress.org/core/handbook/coding-standards/).


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
