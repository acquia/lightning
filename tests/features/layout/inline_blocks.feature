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
    And I scroll to the '.ipe-block-form form input[name="op"]' element
    And I press "Save"
    And I save the layout
    And I visit "/admin/structure/block"
    Then I should not see "I am inline"

  @a98dca7a
  Scenario: Inline blocks should be rendered in the published content
    Given I am logged in as a user with the landing_page_creator,landing_page_reviewer roles
    And landing_page content:
      | type         | title  | path    | moderation_state |
      | landing_page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I create a basic block
    And I enter "I am inline" for "Block description"
    And I put "Here be dragons." into CKEditor
    And I scroll to the '.ipe-block-form form input[name="op"]' element
    And I press "Save"
    And I save the layout
    And I publish the page
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
    And I save the layout
    And I publish the page
    And I create a draft revision
    And I configure the "I am inline" block
    And I put "Here be dragonflies." into CKEditor
    And I scroll to the '.ipe-block-form form input[name="op"]' element
    And I press "Update"
    And I save the layout
    And I click "View"
    Then I should see "Here be dragons."
    And I should not see "Here be dragonflies."

  @d98cbdaf
  Scenario: Inline blocks can be placed into a specific region
    Given I am logged in as a user with the landing_page_creator role
    And landing_page content:
      | type         | title  | path    | moderation_state |
      | landing_page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I switch to the "Two column" layout from the "Columns: 2" category
    And I create a basic block
    And I enter "I am inline" for "Block description"
    And I put "Here be dragons." into CKEditor
    And select "second" from "Region"
    And I press "Save"
    Then I should see an inline_entity block in the second region

  @0946f50a
  Scenario: Quick editing an inline block immediately after creating it
    Given I am logged in as a user with the landing_page_creator role
    And landing_page content:
      | type         | title  | path    | moderation_state |
      | landing_page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I create a basic block
    And I enter "I am inline" for "Block description"
    And I put "Here be dragons." into CKEditor
    And I scroll to the '.ipe-block-form form input[name="op"]' element
    And I press "Save"
    And I show all contextual links
    And I quick edit "I am inline"
    And I edit the body field
    Then the body field should contain "Here be dragons."

  @cea9cfef
  Scenario: The most recent revision should be loaded when quick editing an inline block
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
    And I save the layout
    And I publish the page
    And I create a draft revision
    And I configure the "I am inline" block
    And I put "Here be dragonflies." into CKEditor
    And I scroll to the '.ipe-block-form form input[name="op"]' element
    And I press "Update"
    And I save the layout
    And I show all contextual links
    And I quick edit "I am inline"
    And I edit the body field
    Then the editable body field should contain "Here be dragonflies."
