@lightning @api
Feature: JSON API for decoupled applications

  @23138ee5
  Scenario: Viewing a content entity as JSON
    Given I am logged in as a user with the administrator role
    And page content:
      | title  |
      | Foobar |
    When I visit "/admin/content"
    And I click "View JSON"
    Then the response status code should be 200

  @160f8533
  Scenario Outline: Viewing a config entity as JSON
    Given I am logged in as a user with the administrator role
    When I visit "<url>"
    And I click "View JSON"
    Then the response status code should be 200

    Examples:
      | url                      |
      | /admin/structure/contact |
      | /admin/structure/media   |
      | /admin/structure/types   |
      | /admin/structure/views   |
