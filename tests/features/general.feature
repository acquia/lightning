Feature: General functionality
  Scenario: Anonymous users cannot create content
    Given I am on "/node/add"
    Then I should see "You are not authorized to access this page."
      And I should not see "Add content"

  Scenario: Anonymous users cannot view user profiles
    Given I am on "/user/1"
    Then I should see "You are not authorized to access this page."
      And I should see "Login"
