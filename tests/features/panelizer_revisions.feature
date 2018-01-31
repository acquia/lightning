@lightning @layout @workflow @api
Feature: Showing the Panels IPE interface on the latest content revision only

  @page @aef46ea6
  Scenario: Showing the Panels IPE interface on the latest content revision only
    Given I am logged in as a user with the "create landing_page content, edit own landing_page content, access panels in-place editing, administer panelizer node landing_page content, view own unpublished content, view landing_page revisions" permissions
    And I have panelized the page node type
    And page content:
      | title  | body                          | path    | moderation_state |
      | Foobar | This is the initial revision! | /foobar | draft            |
    When I visit "/foobar"
    And I visit the edit form
    And I enter "This is the second revision!" for "body[0][value]"
    And I press "Save"
    And I visit the current revision
    Then I should see a "#panels-ipe-content" element
    And I visit the 2nd revision
    And I should not see a "#panels-ipe-content" element
    And I unpanelize the page node type

  @landing-page @javascript @errors @988f4ee4
  Scenario: Reverting a unpublished revision of a panelized page to the default layout preserves the latest default revision
    Given I am logged in as a user with the administrator role
    And landing_page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I place the "entity_field:node:uuid" block from the "Content" category
    And I save the layout
    And I visit the edit form
    And I select "Published" from "moderation_state[0][state]"
    And I press "Save"
    And I visit the edit form
    And I select "Draft" from "moderation_state[0][state]"
    And I press "Save"
    And I revert the layout
    And I visit "/user/logout"
    And I visit "/foobar"
    Then I should see a "entity_field:node:uuid" block
