@lightning @api
Feature: Enhancements to display modes and related displays

  Scenario: Users are notified that an internal view mode is internal
    Given I am logged in as a user with the "administer node display" permission
    When I customize the RSS display of the page node type
    And I configure the RSS display of the page node type
    Then I should see "This display is internal and will not be seen by normal users."
    And I should not see a "Panelize this view mode" field
