@lightning @workflow @api
Feature: Workflow moderation states
  As a site administator, I need to be able to manage moderation states for
  content.

  @c9391f57
  Scenario: Anonymous users should not be able to access content in an unpublished, non-draft state.
    Given page content:
      | title             | path   | moderation_state |
      | Moderation Test 1 | /mod-1 | review           |
    When I go to "/mod-1"
    Then the response status code should be 403

  @b3ca1fae
  Scenario: Users with permission to transition content between moderation states should be able to see content in an unpublished, non-draft state.
    Given I am logged in as a user with the "view any unpublished content" permission
    And page content:
      | title             | path   | moderation_state |
      | Moderation Test 2 | /mod-2 | review           |
    When I visit "/mod-2"
    Then the response status code should be 200

  @03ebc3ee
  Scenario: Publishing an entity by transitioning it to a published state
    Given I am logged in as a user with the "view any unpublished content,use editorial transition review,use editorial transition publish,create page content,edit any page content,create url aliases" permissions
    And page content:
      | title             | path   | moderation_state |
      | Moderation Test 3 | /mod-3 | review           |
    And I visit "/mod-3"
    And I visit the edit form
    And I select "Published" from "moderation_state[0][state]"
    And I press "Save"
    And I visit "/user/logout"
    And I visit "/mod-3"
    Then the response status code should be 200

  @c0c17d43
  Scenario: Transitioning published content to an unpublished state
    Given I am logged in as a user with the "use editorial transition publish,use editorial transition archive,create page content,edit any page content,create url aliases" permissions
    And page content:
      | title             | path   | moderation_state |
      | Moderation Test 4 | /mod-4 | published        |
    And I visit "/mod-4"
    And I visit the edit form
    And I select "Archived" from "moderation_state[0][state]"
    And I press "Save"
    And I visit "/user/logout"
    And I go to "/mod-4"
    Then the response status code should be 403

  @cead87f0
  Scenario: Filtering content by moderation state
    Given I am logged in as a user with the "access content overview" permission
    And page content:
      | title          | moderation_state |
      | John Cleese    | review           |
      | Terry Gilliam  | review           |
      | Michael Palin  | published        |
      | Graham Chapman | published        |
      | Terry Jones    | draft            |
      | Eric Idle      | review           |
    When I visit "/admin/content"
    And I select "In review" from "moderation_state"
    And I apply the exposed filters
    Then I should see "John Cleese"
    And I should see "Terry Gilliam"
    And I should not see "Michael Palin"
    And I should not see "Graham Chapman"
    And I should not see "Terry Jones"
    And I should see "Eric Idle"

  @6a1db3b1
  Scenario: Examining the moderation history of a piece of content
    Given I am logged in as a user with the administrator role
    And page content:
      | title           | moderation_state | path     |
      | Samuel L. Ipsum | draft            | /slipsum |
    When I visit "/slipsum"
    And I visit the edit form
    And I select "In review" from "moderation_state[0][state]"
    And I press "Save"
    And I visit the edit form
    And I select "Published" from "moderation_state[0][state]"
    And I press "Save"
    And I click "History"
    Then I should see "Set to draft"
    And I should see "Set to review"
    And I should see "Set to published"

  @javascript @763fbb2c
  Scenario: Quick edit a forward revision
    Given I am logged in as a user with the administrator role
    And page content:
      | title | moderation_state | path   |
      | Squid | published        | /squid |
    When I visit "/squid"
    And I visit the edit form
    And I select "Draft" from "moderation_state[0][state]"
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
    And I select "- Any -" from "moderation_state"
    And I apply the exposed filters
    Then I should see "Lazy Lummox"

  @084ca18d
  Scenario: Content types do not display the Published checkbox once they are moderated
    Given node_type entities:
      | type   | name   |
      | foobar | Foobar |
    And I am logged in as a user with the administrator role
    When I visit "/admin/config/workflow/workflows/manage/editorial/type/node"
    And I check the box "bundles[foobar]"
    And I press "Save"
    And I visit "/node/add/foobar"
    Then I should see the "Save" button
    But I should not see a "status[value]" field
    And I should not see the "Save and publish" button
    And I should not see the "Save as unpublished" button

  @d0f9aaa8
  Scenario: Unmoderated content types have normal submit buttons
    Given node_type entities:
      | type          | name          |
      | not_moderated | Not Moderated |
    And I am logged in as a user with the "administer nodes,create not_moderated content" permissions
    When I visit "/node/add/not_moderated"
    Then I should see the "Save" button
    And the "Published" checkbox should be checked
    And I should not see the "Save and publish" button
    And I should not see the "Save as unpublished" button

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

  @7cef449b
  Scenario: Unmoderated content types have the "Create new revision" Checkbox
    Given node_type entities:
      | type          | name          |
      | not_moderated | Not Moderated |
    And not_moderated content:
      | title      | path        |
      | Deft Zebra | /deft-zebra |
    And I am logged in as a user with the administrator role
    When I visit "/deft-zebra"
    And I click "Edit"
    Then I should see "Create new revision"
