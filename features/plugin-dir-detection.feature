Feature: Test that the command will accept a custom destination or use the current folder

  Background:
    Given a WP install

  Scenario: if not passed a --dir parameter the command should use the current folder
    Given the next command is called with the `--dry-run` parameter
    When I run `wp wpb-scaffold plugin-tests`
    Then STDOUT should contain:
        """
        Plugin tests will be scaffolded in the current working folder.
        """

    Scenario: if passed the path to a folder that's not valid the command should fail
      Given the next command is called with the `--dry-run` parameter
      When I run `wp wpb-scaffold plugin-tests --dir=/some/inexistent/dir`
      Then STDERR should contain:
        """
        Destination folder '/some/inexistent/dir' is not accessible or does not exist.
        """

    Scenario: if passed the path to a valid destination folder the command should use it
      Given the next command is called with the `--dry-run` parameter
      Given the next command is called with the `--dir` parameter
      Given the value of the parameter is `/` from data
      When I run `wp wpb-scaffold plugin-tests`
      Then STDOUT should contain:
        """
        Plugin tests will be scaffolded in the specified folder
        """
