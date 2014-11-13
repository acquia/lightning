Feature: Lightning Demo

  @api
  Scenario: Ensure the lightning homepage has been built
    Given I am an anonymous user
    And I am on the homepage
    Then I should see "Lightning"
    And I should see "A fast and feature-rich Drupal distributon"
    And I should see "Curated by Acquia"
    And I should see "Welcome to a faster, feature-rich Drupal."
    And I should see "Learn more about Lightning's Capabilities"
    And I should see "Lightning Fast"
    And I should see "Feature Rich"
    And I should see "Powered By Drupal"
    And I should see "Editors"
    And I should see "Workflows"
    And I should see "Layout"
    And I should see "Preview"
    When I follow "Editors"
    Then I should see "Lightning Capabilities"
    And I should see "Content Editor Focused"
    And I should see "Editorial Workflows"
    And I should see "Flexible Layout Control"
    And I should see "Advanced Content Preview"
