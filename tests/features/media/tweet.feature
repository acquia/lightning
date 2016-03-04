@lightning @media @api
Feature: Twitter media assets
  A media asset representing a tweet.

  Scenario: Creating a tweet
    Given I am logged in as a user with the "media_creator" role
    When I visit "/media/add"
    And I click "Tweet"
    Then I should see "Tweet"
    And I should see "Save to my media library"

  Scenario: Viewing a tweet as an anonymous user
    Given tweet media from embed code:
    """
    https://twitter.com/webchick/status/672110599497617408
    """
    And I am an anonymous user
    When I visit a media entity of type tweet
    Then I should get a 200 HTTP response
