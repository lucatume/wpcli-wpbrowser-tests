Feature: Test that the command will accept a custom destination or use the current folder

  Background:
    Given a WP install

  Scenario: if not passed a --dir parameter or theme argument the command should show an error message
    Given the next command is called with the `--dry-run` option
    When I run `wp wpb-scaffold theme-tests`
    Then STDERR should not be empty

    Scenario: if passed the path to a folder that's not valid the command should fail
      Given the next command is called with the `--dry-run` option
      When I run `wp wpb-scaffold theme-tests --dir=/some/inexistent/dir`
      Then STDERR should contain:
        """
        Invalid destination folder '/some/inexistent/dir' specified
        """

    Scenario: if passed the path to a valid destination --dir parameter the command should use it
      Given the next command is called with the `--dry-run` option
      Given the next command is called with the `--dir` parameter
      Given the value of the parameter is `/temp` from data
      When I run `wp wpb-scaffold theme-tests`
      Then STDOUT should contain:
        """
        Scaffolding theme tests in the folder
        """

    Scenario: if passed a --dir parameter and a theme argument the command should use the --dir parameter
      Given the next command is called with the `--dry-run` option
      Given the next command is called with the `--dir` parameter
      Given the value of the parameter is `/temp` from data
      Given the 'some-theme' theme folder exists
      When I run `wp wpb-scaffold theme-tests some-theme`
      Then STDOUT should contain:
        """
        Scaffolding theme tests in the folder
        """

    Scenario: if passed a theme argument that's not valid the command should display an error
      Given the next command is called with the `--dry-run` option
      Given the 'some-theme' theme folder does not exist
      When I run `wp wpb-scaffold theme-tests some-theme`
      Then STDERR should contain:
        """
        Invalid theme slug specified
        """

    Scenario: if passed a valid theme argument the command should scaffold the tests in the theme folder
      Given the next command is called with the `--dry-run` option
      Given the 'some-theme' theme folder exists
      When I run `wp wpb-scaffold theme-tests some-theme`
      Then STDOUT should contain:
        """
        Scaffolding theme tests in 'some-theme' folder
        """
