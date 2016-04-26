@lightning @media @api
Feature: Video media assets
  A media asset representing an externally hosted video.

  Scenario: Creating a video
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add"
    And I click "Video"
    Then I should see "Video"
    And I should see "Save to my media library"

  Scenario: Viewing a video as an anonymous user
    Given video media from embed code:
    """
    <iframe width="560" height="315" src="https://www.youtube.com/embed/ktCgVopf7D0" frameborder="0" allowfullscreen></iframe>
    """
    And I am an anonymous user
    When I visit a media entity of type video
    Then I should get a 200 HTTP response

  @javascript
  Scenario: Creating a video in CKEditor from an embed code
    Given I am logged in as a user with the page_creator,media_creator roles
    When I go to "/node/add/page"
    And I open the CKEditor media widget
    And I click "Create Embed"
    And I enter the embed code "https://www.youtube.com/watch?v=DyFYUKBEZAg"
    And I submit the media widget
    Then CKEditor should match "/data-entity-id=.?[0-9]+.?/"
