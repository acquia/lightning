@lightning @api
Feature: Lightning Roles and related config

  Scenario: Administrator Role select list should be present in Account Settings
    Given I am logged in as a user with the administrator role
    When I visit "/admin/config/people/accounts"
    Then I should see "This role will be automatically assigned new permissions whenever a module is enabled."