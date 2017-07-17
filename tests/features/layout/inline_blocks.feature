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
    Then I should not see "I am inilne"
