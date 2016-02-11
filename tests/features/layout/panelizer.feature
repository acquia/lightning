@lightning @layout
Feature: Panelizer

  @api
  Scenario: Opting in to panelization for content
    Given I am logged in as a user with the "administer node display" permission
    When I visit "/admin/structure/types/manage/page/display"
    Then the "panelizer[enable]" checkbox should be checked
    And the "panelizer[custom]" checkbox should be checked
