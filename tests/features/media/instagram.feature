@lightning @media
Feature: Instagram media assets
  A media asset representing an Instagram post.

  @api
  Scenario: Creating an Instagram media entity
    Given I am logged in as a user with the "create media" permission
    When I visit "/media/add"
    And I click "Instagram"
    Then I should see "Instagram post"
    And I should see "Save to my media library"

  @api
  Scenario: Viewing an Instagram post as an anonymous user
    Given I am an anonymous user
    When I visit "/media/1"
    Then I should get a 200 HTTP response
