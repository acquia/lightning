@lightning @api @layout
Feature: Granting and revoking layout permissions when content types are added or deleted

  @beta4
  Scenario: Granting permissions when a content type is created
    Given I am logged in as a user with the "administer content types,administer permissions" permissions
    When I create a foo content type
    Then the layout_editor role should have permission to:
    """
    administer panelizer node foo content
    administer panelizer node foo layout
    """
    And the layout_manager role should have permission to "administer panelizer node foo defaults"

  @beta4
  Scenario: Revoking permissions when a content type is deleted
    Given I am logged in as a user with the "administer content types,administer permissions" permissions
    And I have created a foo content type
    When I visit "/admin/structure/types/manage/foo/delete"
    And I press "Delete"
    Then the layout_editor role should not have permission to:
    """
    administer panelizer node foo content
    administer panelizer node foo layout
    """
    And the layout_manager role should not have permission to "administer panelizer node foo defaults"
