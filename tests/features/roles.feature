@lightning @core @api
Feature: User roles and related config

  @4fde82a9
  Scenario: Describing a role
    Given I am logged in as a user with the "access administration pages,administer users,administer permissions" permissions
    When I visit "/admin/people/roles/add"
    And I enter "Foobaz" for "Role name"
    And I enter "foobaz" for "id"
    And I enter "I am godd here" for "Description"
    And I press "Save"
    And I visit "/user"
    And I click "Edit"
    And I press "Save"
    Then I should see "I am godd here"
    # Clean up.
    And I visit "/admin/people/roles/manage/foobaz/delete"
    And I press "Delete"
