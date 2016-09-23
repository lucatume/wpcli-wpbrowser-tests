Feature: Test that WPBrowser command exists and can help.

    Scenario: the wpb-scaffold help command displays usage information
      Given a WP install

      When I run `wp wpb-scaffold help`
      Then STDOUT should contain:
      """
      usage: wp wpb-scaffold plugin-tests my-plugin
         or: wp wpb-scaffold theme-tests my-theme
      """
