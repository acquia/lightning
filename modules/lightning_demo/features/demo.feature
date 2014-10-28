Feature: Lightning Demo

  @api
  Scenario: Ensure the lightning homepage has been built
    Given I am an anonymous user
    And I am on the homepage
    Then I should see "Welcome to a faster, feature-rich Drupal."
    Then I should see "Learn more about Lightning's Capabilities"
