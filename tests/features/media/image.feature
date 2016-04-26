@lightning @lightning_media @api
  Feature: Image media assets
    A media asset representing a locally hosted image.

  Scenario: Creating an image
    Given I am logged in as a user with the media_creator role
    When I visit "/media/add"
    And I click "Image"
    Then I should see "Image"
    And I should see "Save to my media library"

#  @javascript
#  Scenario: Uploading an image from within CKEditor
#    Given I am logged in as a user with the "administrator" role
#    When I visit "/node/add/page"
#    And I open the CKEditor media widget
#    And I click "Upload Image"
#    And I upload "puppy.jpg" to my media library
#    And I submit the media widget
#    Then CKEditor should match "/data-entity-id=.?[0-9]+.?/"
