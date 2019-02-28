@lightning @api
Feature: Showing the Panels IPE interface on the latest content revision only

  @javascript @errors @988f4ee4
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

  @aef46ea6 @orca_public
  Scenario: Showing the in-place editor on the latest content revision only
    Given I am logged in as a user with the landing_page_creator role
    And landing_page content:
      | title  | body              | path    |
      | Foobar | Initial revision. | /foobar |
    When I visit "/foobar"
    And I visit the edit form
    And I enter "Second revision" for "Description"
    And I press "Save"
    And I visit the current revision
    Then I should see a "#panels-ipe-content" element
    And I visit the 2nd revision
    And I should not see a "#panels-ipe-content" element
