@lightning @media @api
Feature: Video media assets
  A media asset representing an externally hosted video.

  Scenario: Creating a video
    Given I am logged in as a user with the "create media" permission
    When I visit "/media/add"
    And I click "Video"
    Then I should see "Video"
    And I should see "Save to my media library"

  Scenario: Viewing a video as an anonymous user
    Given I am an anonymous user
    When I visit a media entity of type video
    Then I should get a 200 HTTP response

  @javascript
  Scenario: Creating a video in CKEditor from an embed code
    Given I am logged in as a user with the "administrator" role
    When I go to "/node/add/page"
    And I wait for AJAX to finish
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    And I wait for AJAX to finish
    And I click "Create Embed"
    And I enter "https://www.youtube.com/watch?v=DyFYUKBEZAg" for "embed_code"
    # Wait for the server to turn the embed code into an entity.
    And I wait for AJAX to finish
    And I press "Place"
    # Wait for the embed to complete.
    And I wait for AJAX to finish
    Then CKEditor "edit-body-0-value" should match "/data-entity-id=.?[0-9]+.?/"
