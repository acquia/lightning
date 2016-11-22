@lightning @api @workflow @errors
Feature: Scheduled updates to content

  @javascript
  Scenario: Publishing a node that is scheduled to be published in the past
    Given I am logged in as a user with the administrator role
    And page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I click "Edit draft"
    And I select "Needs Review" from "Moderation state"
    And I schedule the node to be published at "09-19-1984 08:57:00AM"
    And I should see "Update Date/time"
    And I press "Save"
    And I visit "/admin/config/workflow/schedule-updates/run"
    And I press "Run Updates"
    And I should see "Results: 1 update(s) were performed"
    And I visit "/user/logout"
    And I visit "/foobar"
    Then I should not see "Access denied"

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
