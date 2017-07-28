@lightning @workflow @api
Feature: Workflow moderation states
  As a site administator, I need to be able to manage moderation states for
  content.

  @c9391f57
  Scenario: Anonymous users should not be able to access content in an unpublished, non-draft state.
    Given page content:
      | title             | path   | moderation_state |
      | Moderation Test 1 | /mod-1 | needs_review     |
    When I go to "/mod-1"
    Then the response status code should be 403

  @b3ca1fae
  Scenario: Users with permission to transition content between moderation states should be able to see content in an unpublished, non-draft state.
    Given I am logged in as a user with the "view any unpublished content" permission
    And page content:
      | title             | path   | moderation_state |
      | Moderation Test 2 | /mod-2 | needs_review     |
    When I visit "/mod-2"
    Then the response status code should be 200

  @03ebc3ee
  Scenario: Publishing an entity by transitioning it to a published state
    Given I am logged in as a user with the "view any unpublished content,use draft_needs_review transition,use needs_review_published transition,create page content,edit any page content,create url aliases" permissions
    And page content:
      | title             | path   | moderation_state |
      | Moderation Test 3 | /mod-3 | needs_review     |
    And I visit "/mod-3"
    And I click "Edit draft"
    And I select "Published" from "Moderation state"
    And I press "Save"
    And I visit "/user/logout"
    And I visit "/mod-3"
    Then the response status code should be 200

  @c0c17d43
  Scenario: Transitioning published content to an unpublished state
    Given I am logged in as a user with the "use draft_published transition,use published_archived transition,create page content,edit any page content,create url aliases" permissions
    And page content:
      | title             | path   | moderation_state |
      | Moderation Test 4 | /mod-4 | published        |
    And I visit "/mod-4"
    And I click "New draft"
    And I select "Archived" from "Moderation state"
    And I press "Save"
    And I visit "/user/logout"
    And I go to "/mod-4"
    Then the response status code should be 403

  @cead87f0
  Scenario: Filtering content by moderation state
    Given I am logged in as a user with the "access content overview" permission
    And page content:
      | title          | moderation_state |
      | John Cleese    | needs_review     |
      | Terry Gilliam  | needs_review     |
      | Michael Palin  | published        |
      | Graham Chapman | published        |
      | Terry Jones    | draft            |
      | Eric Idle      | needs_review     |
    When I visit "/admin/content"
    And I select "Needs Review" from "Status"
    And I press "Filter"
    Then I should see "John Cleese"
    And I should see "Terry Gilliam"
    And I should not see "Michael Palin"
    And I should not see "Graham Chapman"
    And I should not see "Terry Jones"
    And I should see "Eric Idle"

  @javascript @763fbb2c
  Scenario: Quick edit a forward revision
    Given I am logged in as a user with the "administrator" role
    And page content:
      | title | moderation_state | path   |
      | Squid | published        | /squid |
    When I visit "/squid"
    And I click "New draft"
    And I select "Draft" from "Moderation state"
    And I press "Save"
    And I wait 2 seconds
    Then I should see a "system_main_block" block with a "quickedit" contextual link

  @35d54919
  Scenario: Unmoderated content types are visible in the Content view
    Given node_type entities:
      | type          | name          |
      | not_moderated | Not Moderated |
    And not_moderated content:
      | title       |
      | Lazy Lummox |
    And I am logged in as a user with the administrator role
    When I visit "admin/content"
    Then I should see "Lazy Lummox"

  @d0f9aaa8
  Scenario: Unmoderated content types have normal submit buttons
    Given node_type entities:
      | type          | name          |
      | not_moderated | Not Moderated |
    And I am logged in as a user with the "administer nodes,create not_moderated content" permissions
    When I visit "/node/add/not_moderated"
    Then I should see the "Save and publish" button
    And I should see the "Save as unpublished" button

  @7cef449b
  Scenario: Unmoderated content types have the "Create new revision" Checkbox
    Given node_type entities:
      | type          | name          |
      | not_moderated | Not Moderated |
    And not_moderated content:
      | title       | path       |
      | Deft Zebra  | /deft-zebra |
    And I am logged in as a user with the administrator role
    When I visit "/deft-zebra"
    And I click "Edit"
    Then I should see "Create new revision"
