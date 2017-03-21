@lightning @media @api
Feature: Media bundles

  @0ed5e2e9
  Scenario: Automatically attaching the "Save to my media library" field to new media bundles
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/media/add"
    And I enter "Foobaz" for "Label"
    And I enter "foobaz" for "id"
    And I press "Save media bundle"
    And I visit "/media/add"
    And I click "Foobaz"
    Then I should see "Save to my media library"
    # Clean up...
    And I visit "/admin/structure/media/manage/foobaz/delete"
    And I press "Delete"
