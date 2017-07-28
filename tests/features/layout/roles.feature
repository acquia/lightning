@lightning @api @layout
Feature: Responsibility-based user roles for editing and managing layouts

  @beta5 @0810736c
  Scenario: Layout managers lose permission to administer Panelizer defaults for deleted node types
    Given I am logged in as a user with the administrator role
    And node_type entities:
      | type | name |
      | foo  | foo  |
    When I visit "/admin/structure/types/manage/foo/delete"
    And I press "Delete"
    And I visit "/admin/people/permissions"
    Then the layout_manager role should not have permission to "administer panelizer node foo defaults"
