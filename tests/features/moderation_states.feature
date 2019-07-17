@lightning @workflow @api
Feature: Workflow moderation states
  As a site administator, I need to be able to manage moderation states for
  content.

  @14ddcc9d
  Scenario Outline: Moderated content types do not show the Published checkbox
    Given I am logged in as a user with the <role> role
    When I visit "/node/add/<node_type>"
    Then I should see the "Save" button
    But I should not see a "status[value]" field
    And I should not see the "Save and publish" button
    And I should not see the "Save as unpublished" button

    Examples:
      | node_type    | role                 |
      | page         | page_creator         |
      | page         | administrator        |
      | landing_page | landing_page_creator |
      | landing_page | administrator        |
