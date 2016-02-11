@api @lightning @media @javascript @ckeditor
Feature: Media library CKEditor widget

  Scenario: Opening the media library
    Given I am logged in as a user with the "administrator" role
    When I go to "node/add/page"
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    Then I should see "Media Library"

  Scenario: Filtering the media library by media type
    Given I am logged in as a user with the "administrator" role
    When I go to "/node/add/page"
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    And I select "image" from "lightning-media-bundle"
    Then I should see "There are no items to display."
