@api @lightning @media
Feature: Media library CKEditor widget

  @javascript
  Scenario: Opening the media library
    Given I am logged in as a user with the "administrator" role
    When I go to "node/add/page"
    And wait 1 second
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    Then I should see "Media Library"
