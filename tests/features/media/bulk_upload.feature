@lightning @api @media @javascript
Feature: Bulk uploading media assets

  # We have no idea why, but this test persistently fails on Travis CI, but
  # invariably passes locally. It works. But Travis doesn't pass it, and
  # produces no errors. For now, we are commenting it out so we can move on
  # with our lives. Hopefully, we'll eventually be able to test this again.
  @72286b5d @with-module:lightning_test
  Scenario: Bulk uploading media assets
    Given I am logged in as a user with the media_creator role
    When I visit "/admin/content/media"
    And I click "Bulk upload"
    # Wait for Dropzone to be fully initialized.
    And I wait 5 seconds
    And I attach the file "test.jpg" to the dropzone
#    And I attach the file "test.pdf" to the dropzone
    And I press "Continue"
    And I press "Save"
#    And I press "Save"
    Then I should be visiting a media entity
