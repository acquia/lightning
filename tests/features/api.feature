@lightning @api
Feature: JSON API for decoupled applications

  @6244afea
  Scenario Outline: Viewing a config entity as JSON
    Given I am logged in as a user with the administrator role
    When I visit "<url>"
    And I click "View JSON"
    Then the response status code should be 200

    Examples:
      | url                      |
      | /admin/structure/contact |
      | /admin/structure/media   |
