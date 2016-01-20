Feature: Features should be in default state
  In order for the site to be reliable when installing
  As an administrator
  The features should be in default state without conflicts

  @api
  Scenario: Check the features
    When I am logged in as a user with the "administrator" role
    And I go to "/admin/structure/features"
    Then I should not see "Conflicts with"