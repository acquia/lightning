@lightning @media @api @errors
Feature: Image media assets
  A media asset representing a locally hosted image.

  @image @javascript @09232f9f
  Scenario: Creating an image
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/image"
    And I attach the file "test.jpg" to "Image"
    And I wait for AJAX to finish
    And I enter "Foobaz" for "Media name"
    And I press "Save and publish"
    # Queue the image for deletion now so it will be deleted even if the
    # test fails.
    And I queue the latest media entity for deletion
    Then I should be visiting a media entity
    And I should see "Foobaz"

  @image @javascript @b23435a5
  Scenario: Uploading an image to be ignored by the media library
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/image"
    And I attach the file "test.jpg" to "Image"
    And I wait for AJAX to finish
    And I enter "Blorg" for "Media name"
    And I uncheck the box "Save to my media library"
    And I press "Save and publish"
    # Queue the image for deletion now so it will be deleted even if the
    # test fails.
    And I queue the latest media entity for deletion
    And I visit "/entity-browser/iframe/media_browser"
    And I enter "Blorg" for "Keywords"
    And I press "Apply"
    And I wait for AJAX to finish
    Then I should see "There are no media items to display."
