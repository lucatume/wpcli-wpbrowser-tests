Feature: Test that WPBrowser command exists and can help.

  Scenario: the wpb-scaffold command exists
    Given a WP install

    When I run `wp wpb-scaffold`
    Then STDOUT should contain:
      """
      usage: wp wpb-scaffold plugin-tests
         or: wp wpb-scaffold theme-tests
      """

    Scenario: the wpb-scaffold help command displays usage information
      Given a WP install

      When I run `wp wpb-scaffold help`
      Then STDOUT should contain:
      """
      usage: wp wpb-scaffold plugin-tests
         or: wp wpb-scaffold theme-tests
      """
