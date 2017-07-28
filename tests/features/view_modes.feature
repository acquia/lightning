@lightning @core @api
Feature: Enhancements to display modes and related displays

  @19ff1499 @with-module:internal_display_mode_test
  Scenario: Users are notified that an internal view mode is internal
    Given I am logged in as a user with the "administer node display" permission
    When I visit "/admin/structure/types/manage/page/display"
    And I check the box "Foobaz"
    And I press "Save"
    And I click "Foobaz"
    Then I should see the warning message "This display is internal and will not be seen by normal users."
    And I should not see a "Panelize this view mode" field
