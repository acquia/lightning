@lightning @workflow @api
Feature: Workflow moderation states
  As a site administator, I need to be able to manage moderation states for
  content.

  Scenario: Creating a moderation state
    Given I am logged in as a user with the "administer moderation states" permission
    When I visit "/admin/structure/workbench-moderation/states/add"
    And I enter "Wambooli" for "Label"
    And I enter "wambooli" for "id"
    And I press the "Save" button
    Then I should be on "/admin/structure/workbench-moderation/states"
    And I should see "Wambooli"

  Scenario: Automatically creating transitions for a new moderation state
    Given I am logged in as a user with the "administer moderation states,administer moderation state transitions" permissions
    When I visit "/admin/structure/workbench-moderation/states/add"
    And I enter "Blorf" for "Label"
    And I enter "blorf" for "id"
    And I check the box "transitions[needs_review]"
    And I check the box "transitions[published]"
    And I press the "Save" button
    And I visit "/admin/structure/workbench-moderation/transitions"
    Then I should see "Blorf » Blorf"
    And I should see "Blorf » Needs Review"
    And I should see "Blorf » Published"
    And I should not see "Blorf » Draft"
