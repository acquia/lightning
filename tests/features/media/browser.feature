@lightning @media @api @javascript @errors
Feature: An entity browser for media assets

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
    Then 1 element should match "[data-selectable].selected"
    # No choices are ever disabled in a single-cardinality entity browser.
    And nothing should match "[data-selectable].disabled"

  @test_module
  Scenario: Using the media browser in a media reference field
    Given I am logged in as a user with the page_creator,media_creator roles
    When I visit "/node/add/page"
    Then I should see an "iframe[name^='entity_browser_iframe_media_browser']" element
