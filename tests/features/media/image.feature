@lightning @lightning_media @api
  Feature: Image media assets
    A media asset representing a locally hosted image.

  Scenario: Creating an image
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add"
    And I click "Image"
    Then I should see "Image"
    And I should see "Save to my media library"

