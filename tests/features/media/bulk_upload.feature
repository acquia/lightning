@lightning @api @media @javascript
Feature: Bulk uploading media assets

  @72286b5d @with-module:lightning_test
  Scenario: Bulk uploading media assets
    Given I am logged in as a user with the media_creator role
    When I visit "/admin/content/media"
    And I click "Bulk upload"
    # Wait for Dropzone to be fully initialized.
    And I wait 5 seconds
    And I attach the file "test.jpg" to the dropzone
    And I attach the file "test.pdf" to the dropzone
    And I press "Continue"
    And I press "Save and keep published"
    And I press "Save and keep published"
    Then I should be visiting a media entity
