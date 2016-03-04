@lightning @api @layout
Feature: Responsibility-based user roles for editing and managing layouts

  @beta4
  Scenario: Layout-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    Then I should see "Layout Editor"
    And I should see "Layout Manager"

  @beta4
  Scenario: Users with the Content Creator role can create and edit landing pages and change their layout
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/content_creator"
    # Permissions added by lightning_layout #8002
    Then the content_creator role should have permission to:
    """
    create landing_page content
    delete own landing_page content
    edit own landing_page content
    view landing_page revisions
    administer panelizer node page content
    administer panelizer node landing_page content
    """

  @beta4
  Scenario: Users with the Layout Editor role can change the layout of any content
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/layout_editor"
    Then the layout_editor role should have permission to:
    """
    administer panelizer node page content
    administer panelizer node page layout
    administer panelizer node landing_page content
    administer panelizer node landing_page layout
    access panels in-place editing
    """

  @beta4
  Scenario: Users with the Layout Manager role can administer content type displays and Panelizer defaults
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/layout_manager"
    Then the layout_manager role should have permission to:
    """
    administer node display
    administer panelizer node page defaults
    administer panelizer node landing_page defaults
    """
