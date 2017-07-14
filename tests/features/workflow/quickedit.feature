@lightning @api @workflow @javascript
Feature: Integration of workflows with Quick Edit

  @f2beeeda
  Scenario: Quick Edit should be available for unpublished content
    Given I am logged in as a user with the page_creator role
    And page content:
      | type | title  | path    | moderation_state |
      | page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    Then Quick Edit should be enabled

  @b62c6213
  Scenario: Quick Edit should be disabled for published content
    Given I am logged in as a user with the page_creator,page_reviewer roles
    And page content:
      | type | title  | path    | moderation_state |
      | page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I click "Edit draft"
    And I select "published" from "Moderation state"
    And I press "Save"
    Then Quick Edit should be disabled

  @fb59aafc
  Scenario: Quick Edit should be enabled on forward revisions
    # The content roles do not have the ability to transition content from
    # Published to Draft states.
    Given I am logged in as a user with the administrator role
    And page content:
      | type | title  | path    | moderation_state |
      | page | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I click "Edit draft"
    And I select "published" from "Moderation state"
    And I press "Save"
    And I click "New draft"
    And I select "draft" from "Moderation state"
    And I press "Save"
    And I click "Latest version"
    Then Quick Edit should be enabled
