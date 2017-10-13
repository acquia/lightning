@lightning @media @api
Feature: Media bundles

  # This test expects errors due to MediaTypeForm (in core) not performing a
  # needed isset() check, resulting in harmless PHP notices.
  @0ed5e2e9 @with-module:media_entity_generic @errors
  Scenario: Automatically attaching the "Save to my media library" field to new media bundles
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/media/add"
    And I enter "Foobaz" for "Name"
    And I enter "foobaz" for "id"
    And I select "generic" from "Media source"
    And I press "Save"
    And I visit "/media/add"
    And I click "Foobaz"
    Then I should see "Save to my media library"
    # Clean up...
    And I visit "/admin/structure/media/manage/foobaz/delete"
    And I press "Delete"
