@lightning @api @layout
Feature: Responsibility-based user roles for editing and managing layouts

  @core @landing-page @beta5 @98d7e47a
  Scenario: Layout-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    Then I should see "Landing Page Creator"
    And I should see "Landing Page Reviewer"
    And I should see "Layout Manager"

  @core @page @landing-page @d04aed33
  Scenario: Layout managers have permission to administer Panelizer defaults
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions"
    Then the layout_manager role should have permission to:
      """
      administer node display
      administer panelizer
      administer panelizer node page defaults
      administer panelizer node landing_page defaults
      """

  @beta5 @49090694
  Scenario: Layout managers get permission to administer Panelizer defaults for new node types
    Given I am logged in as a user with the administrator role
    And node_type entities:
      | type | name |
      | foo  | foo  |
    When I visit "/admin/people/permissions"
    Then the layout_manager role should have permission to "administer panelizer node foo defaults"

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
