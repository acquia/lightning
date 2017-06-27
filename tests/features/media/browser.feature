@lightning @media @api @javascript @errors
Feature: An entity browser for media assets

  @twitter @fe9a2c68
  Scenario: Opening the media browser on a pre-existing node
    Given I am logged in as a user with the "page_creator,page_reviewer,media_creator" roles
    And media entities:
      | bundle | name            | embed_code                                                  | status | field_media_in_library |
      | tweet  | Here be dragons | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
    When I visit "/node/add/page"
    And I enter "Blorgzville" for "Title"
    And I open the media browser
    And I select item 1 in the media browser
    And I complete the media browser selection
    And I wait 5 seconds
    And I press "Save"
    And I click "Edit draft"
    And I wait 10 seconds
    And I open the media browser
    And I wait for AJAX to finish
    Then I should see a "form.entity-browser-form" element
    And I queue the latest node entity for deletion

  @twitter @ee4d5a41
  Scenario: Testing cardinality enforcement in the media browser
    Given I am logged in as a user with the page_creator,media_creator roles
    And media entities:
      | bundle | name          | embed_code                                               | status | field_media_in_library |
      | tweet  | Code Wisdom 1 | https://twitter.com/CodeWisdom/status/707945860936691714 | 1      | 1                      |
      | tweet  | Code Wisdom 2 | https://twitter.com/CodeWisdom/status/826500049760821248 | 1      | 1                      |
      | tweet  | Code Wisdom 3 | https://twitter.com/CodeWisdom/status/826460810121773057 | 1      | 1                      |
    When I visit "/node/add/page"
    And I open the media browser
    And I wait 5 seconds
    # There was a bug where AJAX requests would completely break the selection
    # behavior. So let's make an otherwise pointless AJAX request here to guard
    # against regressions...
    And I enter "Pastafazoul!" for "Keywords"
    And I press "Apply"
    And I wait for AJAX to finish
    And I clear "Keywords"
    And I press "Apply"
    And I wait for AJAX to finish
    And I select item 1 in the media browser
    And I select item 2 in the media browser
    Then I should see a "[data-selectable].selected" element
    # No choices are ever disabled in a single-cardinality entity browser.
    And I should see 0 "[data-selectable].disabled" elements

  @with-module:lightning_test @93e7dcf1
  Scenario: Using the media browser for a new media reference field
    Given I am logged in as a user with the administrator role
    And I visit "/admin/structure/types/manage/page/fields/add-field"
    And I select "field_ui:entity_reference:media" from "new_storage_type"
    And I enter "Foobar" for "label"
    # Wait for the machine name to be generated automagically.
    And I wait 3 seconds
    And I press "Save and continue"
    And I press "Save field settings"
    And I check "settings[handler_settings][target_bundles][image]"
    And I wait for AJAX to finish
    And I press "Save settings"
    When I visit "/node/add/page"
    Then I should see an "iframe[name^='entity_browser_iframe_media_browser']" element

  @81cfbefc
  Scenario: Bundle filter is present when no contextual filter is given.
    Given I am logged in as a user with the "page_creator,page_reviewer,media_creator" roles
    When I visit "/node/add/page"
    And I open the media browser
    Then I should see "Type"