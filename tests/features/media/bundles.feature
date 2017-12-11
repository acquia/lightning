@lightning @media @api
Feature: Media bundles

  # This test expects errors due to MediaTypeForm (in core) not performing a
  # needed isset() check, resulting in harmless PHP notices.
  @0ed5e2e9 @with-module:media_entity_generic @errors
  Scenario: Automatically attaching the "Save to my media library" field to new media bundles
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/media/add"
    And I enter "Foobaz" for "Name"
    And I enter "foobaz" for "id"
    And I select "generic" from "Media source"
    And I press "Save"
    And I visit "/media/add"
    And I click "Foobaz"
    Then I should see "Save to my media library"
    # Clean up...
    And I visit "/admin/structure/media/manage/foobaz/delete"
    And I press "Delete"

  @d63d19c4 @javascript
  Scenario: Name field's display can be managed and is hidden by default
    Given I am logged in as a user with the administrator role
    And media entities:
      | bundle | name            | embed_code                                                  | status | field_media_in_library |
      | tweet  | Here be dragons | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
    And node entities:
      | type | title | moderation_state | path |
      | page | Foo  | draft             | /foo |
    When I visit "/foo"
    And I visit the edit form
    And I open the media browser
    And I select item 1
    And I complete the media browser selection
    And I wait 5 seconds
    And I press "Save"
    Then I should be on "/foo"
    And I should not see "Here be dragons"
