@media @api
Feature: Image media assets
  A media asset representing a locally hosted image.

  @javascript
  Scenario: Creating an image
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add/image"
    And I attach the file "puppy.jpg" to "Image"
    And I wait for AJAX to finish
    And I enter "Foobaz" for "Media name"
    And I press "Save and publish"
    Then I should be visiting a media entity
    And I should see "Foobaz"
    And I queue the latest media entity for deletion

#  @javascript
#  Scenario: Uploading an image from within CKEditor
#    Given I am logged in as a user with the "administrator" role
#    When I visit "/node/add/page"
#    And I open the CKEditor media widget
#    And I click "Upload Image"
#    And I upload "puppy.jpg" to my media library
#    And I submit the media widget
#    Then CKEditor should match "/data-entity-id=.?[0-9]+.?/"
