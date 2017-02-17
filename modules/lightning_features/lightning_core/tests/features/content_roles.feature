@lightning @api
Feature: Automatic user roles for creating and managing content types

  Scenario: Automatically creating user roles for a new content type
    Given I am logged in as a user with the administrator role
    And node_type entities:
      | type | name |
      | foo  | foo  |
    And I visit "/admin/people/roles"
    Then I should see "foo Creator"
    And I should see "foo Reviewer"

  Scenario: Automatically deleting user roles for a deleted content type
    Given I am logged in as a user with the administrator role
    And node_type entities:
      | type | name |
      | foo  | foo  |
    When I visit "/admin/structure/types/manage/foo/delete"
    And I press "Delete"
    And I visit "/admin/people/roles"
    Then I should not see "foo Creator"
    And I should not see "foo Reviewer"

  Scenario: Configuring automatic user roles
    Given I am logged in as a user with the administrator role
    When I visit "/admin/config/system/lightning"
    And I uncheck the box "content_roles[reviewer]"
    And I press "Save configuration"
    Then the response status code should be 200
    # Clean up.
    And I check the box "content_roles[reviewer]"
    And I press "Save configuration"
