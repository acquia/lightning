@lightning @layout @api
Feature: Panelizer

  Scenario: Opting in to panelization for content
    Given I am logged in as a user with the "administer node display" permission
    When I visit "/admin/structure/types/manage/page/display"
    Then the "panelizer[enable]" checkbox should be checked
    And the "panelizer[custom]" checkbox should be checked

  Scenario: Panelization enabled by default for basic pages
    Given I am logged in as a user with the "administer nodes,access panels in-place editing,administer panelizer node page content" permission
    And page content:
      | title  | body                                   |
      | Foobar | This is my handle, this is my spout... |
    When I visit a node entity of type page
    Then I should see a "#panels-ipe-content" element
