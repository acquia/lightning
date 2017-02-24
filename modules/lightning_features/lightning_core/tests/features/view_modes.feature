@lightning @api
Feature: Enhancements to display modes and related displays

  Scenario: Users are notified that an internal view mode is internal
    Given I am logged in as a user with the "administer node display" permission
    When I customize the rss display of the page node type
    And I visit "/admin/structure/types/manage/page/display/rss"
    Then I should see "This display is internal and will not be seen by normal users."
    And I should not see a "Panelize this view mode" field

  Scenario: Viewing view mode descriptions when editing view displays
    Given I am logged in as a user with the "access administration pages,administer display modes,administer node display" permissions
    When I visit "/admin/structure/display-modes/view/add/node"
    And I enter "Foobaz" for "Name"
    And I enter "foobaz" for "id"
    And I enter "Behold my glorious view mode" for "Description"
    And I press "Save"
    And I visit "/admin/structure/types/manage/page/display"
    And I check the box "display_modes_custom[foobaz]"
    And I press "Save"
    And I visit "/admin/structure/types/manage/page/display/foobaz"
    Then I should see "Behold my glorious view mode"
    # Clean up.
    And I visit "/admin/structure/display-modes/view/manage/node.foobaz/delete"
    And I press "Delete"
