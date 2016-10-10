Feature: Test that composer existence and accessibility is dealt with.

  Background:
    Given a WP install

  Scenario: if passed a wrong --composer argument it should fail.
    When I run `wp wpb-scaffold plugin-tests --composer=/some/file.foo`
    Then STDERR should contain:
        """
        Error: specified Composer path '/some/file.foo' is not a valid Composer executable.
        """

  Scenario: if passed a --composer args that's not Composer it should fail
    Given the next command is called with the `--composer` parameter
    Given the value of the parameter is `some-file.phar` from data
    When I run `wp wpb-scaffold plugin-tests`
    Then STDERR should contain:
        """
        is not a valid Composer executable.
        """

  @pathEnv @badComposer @current
  Scenario: if global Composer command is not good it should fail
    Given the global $PATH var includes the data dir
    When I run `wp wpb-scaffold plugin-tests`
    Then STDERR should contain:
        """
        'composer' (https://getcomposer.org/) command not found or not good.
        """
