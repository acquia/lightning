@lightning @api @workflow
Feature: Scheduled updates to content

  @a4291af5
  Scenario: Scheduling a moderation state change on a new node
    Given I am logged in as a user with the page_creator role
    When I visit "/node/add/page"
    Then I should not see "Scheduled publication"

  @0e5b60fd
  Scenario: Scheduling a moderation state change on an unmoderated content type
    Given node_type entities:
      | type          | name          |
      | not_moderated | Not Moderated |
    And not_moderated content:
      | title     | path       |
      | Jucketron | /jucketron |
    And I am logged in as a user with the administrator role
    When I visit "/jucketron"
    And I visit the edit form
    Then I should not see "Scheduled publication"

  @020449b3
  Scenario: Publishing a node that is scheduled to be published in the past
    Given I am logged in as a user with the administrator role
    And page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I visit the edit form
    And I enter "1984-09-19" for "scheduled_publication[0][value][date]"
    And I enter "08:57:00" for "scheduled_publication[0][value][time]"
    And I select "In review" from "moderation_state[0][state]"
    And I press "Save"
    And I run cron
    And I visit "/foobar"
    And I visit the edit form
    # The colon after "Current state" is added by CSS, so it cannot be asserted
    # in this test.
    Then I should see "Current state In review"

  # Lightning Scheduler doesn't support bulk updates yet, so this test is
  # disabled for now.
#  @39682068
#  Scenario: Schedule and execute publication of node through bulk scheduled updates
#    Given I am logged in as a user with the administrator role
#    And page content:
#      | title  | path    | moderation_state |
#      | Foobar | /foobar | draft            |
#    When I visit "/admin/content/scheduled-update/add"
#    And I reference node "Foobar" in "entity_ids[0][target_id]"
#    And I enter "1984-09-19" for "update_timestamp[0][value][date]"
#    And I enter "08:57:00" for "update_timestamp[0][value][time]"
#    And I select "published" from "field_moderation_state"
#    And I press "Save"
#    And I run cron
#    And I visit "/user/logout"
#    And I visit "/foobar"
#    Then I should not see "Access denied"
