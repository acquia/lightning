@lightning @lightning_media
  Feature: Image media assets
    A media asset representing a locally hosted image.

@api
  Scenario: Creating an image
    Given I am logged in as a user with the "create media" permission
    When I visit "/media/add"
    And I click "Image"
    Then I should see "Image"
    And I should see "Save to my media library"
