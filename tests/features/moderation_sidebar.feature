@lightning @lightning_workflow @api
Feature: Moderation sidebar

  Scenario: Basic page creator can use moderation sidebar
    Given I am logged in as a user with the page_creator role
    And page content:
      | title | moderation_state | path |
      | Foo   | draft            | /foo |
    When I visit "/foo"
    Then I should see the link "Tasks"
