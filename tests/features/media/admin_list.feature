@lightning @api @media
Feature: Media content list page

  @181ec740
  Scenario: Media filters are present
    Given I am logged in as a user with the "media_creator" role
    And media entities:
      | bundle    | name             | embed_code                                                  | status | field_media_in_library |
      | tweet     | I'm a tweet      | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
      | instagram | I'm an instagram | https://www.instagram.com/p/BaecNGYAYyP/                    | 1      | 1                      |
    When I visit "/admin/content/media"
    Then I should see "Published status"
    And I should see "Source"
    And I should see "Media name"
    And I should see "Language"

  @bd2a222b
  Scenario: Media filters are functional
    Given I am logged in as a user with the "media_creator" role
    And media entities:
      | bundle    | name             | embed_code                                                  | status | field_media_in_library |
      | tweet     | I'm a tweet      | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
      | instagram | I'm an instagram | https://www.instagram.com/p/BaecNGYAYyP/                    | 1      | 1                      |
    When I visit "/admin/content/media"
    And I select "Tweet" from "Source"
    And I apply the exposed filters
    Then I should see "I'm a tweet"
    And I should not see "I'm an instagram"

  @0207232c
  Scenario: Media actions are present
    Given I am logged in as a user with the "media_creator" role
    And media entities:
      | bundle    | name             | embed_code                                                  | status | field_media_in_library |
      | tweet     | I'm a tweet      | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
      | instagram | I'm an instagram | https://www.instagram.com/p/BaecNGYAYyP/                    | 1      | 1                      |
    When I visit "/admin/content/media"
    Then I should see "Action"

  @c292f45d
  Scenario: Media actions are functional
    Given I am logged in as a user with the "administrator" role
    And media entities:
      | bundle    | name             | embed_code                                                  | status | field_media_in_library |
      | tweet     | I'm a tweet      | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
      | instagram | I'm an instagram | https://www.instagram.com/p/BaecNGYAYyP/                    | 1      | 1                      |
    When I visit "/admin/content/media"
    And I should see "I'm a tweet"
    And I should see "I'm an instagram"
    And I select "Delete media" from "Action"
    And I check the box "edit-media-bulk-form-0"
    And I check the box "edit-media-bulk-form-1"
    And I press the "Apply to selected items" button
    And I press the "Delete" button
    Then I should see "Deleted 2 media items."
    And I should not see "I'm a tweet"
    And I should not see "I'm an instagram"
