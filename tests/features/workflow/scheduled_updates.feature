@lightning @api @workflow
Feature: Scheduled updates to content

  Scenario: Schedule and execute publication of node through bulk scheduled updates
    Given I am logged in as a user with the administrator role
    And page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/admin/content/scheduled-update/add"
    And I reference node "Foobar" in "entity_ids[0][target_id]"
    And I enter "1984-09-19" for "update_timestamp[0][value][date]"
    And I enter "08:57:00" for "update_timestamp[0][value][time]"
    And I select "published" from "field_moderation_state_1"
    And I press "Save"
    And I run cron
    And I visit "/user/logout"
    And I visit "/foobar"
    Then I should not see "Access denied"
