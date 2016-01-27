@lightning @media
Feature: Video media assets
  A media asset representing an externally hosted video.

  @api
  Scenario: Creating a video
    Given I am logged in as a user with the "create media" permission
    When I visit "/media/add"
    And I click "Video"
    Then I should see "Video"
    And I should see "Save to my media library"

  @api
  Scenario: Viewing a video as an anonymous user
    Given I am an anonymous user
    When I visit "/media/3"
    Then I should get a 200 HTTP response
