@lightning @layout @api @javascript
Feature: Inline content blocks in a Panels layout

  @751545cc
  Scenario: Inline blocks should not appear in the standard block listing
    Given I am logged in as a user with the landing_page_creator,layout_manager roles
    And landing_page content:
      | type         | title  | path    | moderation_state |
      | landing_page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I create a basic block
    And I enter "I am inline" for "Block description"
    And I press "Save"
    And I wait for AJAX to finish
    And I save the layout
    And I visit "/admin/structure/block"
    Then I should not see "I am inline"

  @a98dca7a
  Scenario: Inline blocks should be rendered in the published content
    Given I am logged in as a user with the landing_page_creator,landing_page_reviewer role
    And landing_page content:
      | type         | title  | path    | moderation_state |
      | landing_page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I create a basic block
    And I enter "I am inline" for "Block description"
    And I put "Here be dragons." into CKEditor
    And I press "Save"
    And I wait for AJAX to finish
    And I save the layout
    And I click "Edit draft"
    And I select "published" from "Moderation state"
    And I press "Save"
    Then I should see "Here be dragons."

  @1ab316e6
  Scenario: Changes to inline blocks in a forward revision should not affect the published revision
    Given I am logged in as a user with the administrator role
    And landing_page content:
      | type         | title  | path    | moderation_state |
      | landing_page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I create a basic block
    And I enter "I am inline" for "Block description"
    And I put "Here be dragons." into CKEditor
    And I scroll to the '.ipe-block-form form input[name="op"]' element
    And I press "Save"
    And I wait for AJAX to finish
    And I save the layout
    And I click "Edit draft"
    And I select "published" from "Moderation state"
    And I press "Save"
    And I click "New draft"
    And I select "draft" from "Moderation state"
    And I press "Save"
    And I configure the "I am inline" block
    And I put "Here be dragonfiles." into CKEditor
    And I press "Update"
    And I save the layout
    And I click "View"
    Then I should see "Here be dragons."
    And I should not see "Here be dragonflies."
