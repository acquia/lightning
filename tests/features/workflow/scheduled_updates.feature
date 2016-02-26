@lightning @api @workflow
Feature: Scheduled updates to content

  @javascript
  Scenario: Publishing a node that is scheduled to be published in the past
    Given I am logged in as a user with the "administrator" role
    And page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I click "Edit draft"
    And I press "Add new entity"
    And I wait for AJAX to finish
    And I enter "1984-09-19" for "scheduled_update[form][inline_entity_form][update_timestamp][0][value][date]"
    And I enter "08:57:00" for "scheduled_update[form][inline_entity_form][update_timestamp][0][value][time]"
    And I select "published" from "scheduled_update[form][inline_entity_form][field_moderation_state]"
    And I press "Create entity"
    And I wait for AJAX to finish
    And I expand the "edit-save" drop button
    And I press "Save and Request Review"
    And I run cron
    And I visit "/user/logout"
    And I visit "/foobar"
    Then I should not see "Access denied"
    And I cleanup the "/foobar" alias
