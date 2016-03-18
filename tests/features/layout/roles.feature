@lightning @api @layout
Feature: Responsibility-based user roles for editing and managing layouts

  @beta5
  Scenario: Layout-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    Then I should see "Landing Page Creator"
    And I should see "Landing Page Reviewer"
    And I should see "Layout Manager"

  @beta5
  Scenario: Layout managers have permission to administer Panelizer defaults
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/layout_manager"
    Then the "layout_manager[administer panelizer node page defaults]" checkbox should be checked
    And the "layout_manager[administer panelizer node landing_page defaults]" checkbox should be checked

  @beta5
  Scenario: Layout managers get permission to administer Panelizer defaults for new node types
    Given I am logged in as a user with the administrator role
    And I have created a foo content type
    When I visit "/admin/people/permissions/layout_manager"
    Then the "layout_manager[administer panelizer node foo defaults]" checkbox should be checked

  @beta5
  Scenario: Layout managers lose permission to administer Panelizer defaults for deleted node types
    Given I am logged in as a user with the administrator role
    And I have created a foo content type
    When I visit "/admin/structure/types/manage/foo/delete"
    And I press "Delete"
    And I visit "/admin/people/permissions/layout_manager"
    Then I should not see a "input[name='layout_manager[administer panelizer node foo defaults]']" element
