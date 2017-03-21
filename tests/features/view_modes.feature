@lightning @core @api
Feature: Enhancements to display modes and related displays

  @19ff1499
  Scenario: Users are notified that an internal view mode is internal
    Given I am logged in as a user with the "administer node display" permission
    When I customize the rss display of the page node type
    And I visit "/admin/structure/types/manage/page/display/rss"
    Then I should see "This display is internal and will not be seen by normal users."
    And I should not see a "Panelize this view mode" field
