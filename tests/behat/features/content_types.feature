Feature: Content types
  @api
  Scenario: Curators can create articles
    Given I am logged in as a user with the "curator" role
      And I am on "/node/add/article"
    When I fill in "Post title" for "title"
    And I fill in "Post body" for "body[und][0][value]"
    And I press "Save as Draft"
    Then I should see "Post title"
      And I should see "Post body"

  @api
  Scenario: Curators can create webforms
    Given I am logged in as a user with the "curator" role
      And I am on "/node/add/webform"
    When I fill in "Webform title" for "title"
    And I press "Save as Draft"
    Then I should see "Webform title"

  @api
  Scenario: Marketers can create landing pages
    Given I am logged in as a user with the "marketer" role
    And I am on "/node/add/landing"
    When I fill in "Landing title" for "title"
    And I press "Save"
    Then I should see "Landing title"