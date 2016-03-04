@lightning @api @media
Feature: Responsibility-based user roles for creating and managing media assets

  @beta4
  Scenario: Media-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    Then I should see "Media Creator"
    And I should see "Media Manager"

  @beta4
  Scenario: Users with the Content Creator role can use the rich text format
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/content_creator"
    # Permissions added by lightning_media #8004
    Then the content_creator role should have permission to "use text format rich_text"

  @beta4
  Scenario: Users with the Content Manager role can use the rich text format
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/content_manager"
    # Permissions added by lightning_media #8004
    Then the content_creator role should have permission to "use text format rich_text"

  @beta4
  Scenario: Users with the Media Creator role can create and edit media
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/media_creator"
    Then the media_creator role should have permission to:
    """
    create media
    delete media
    update media
    """

  @beta4
  Scenario: Users with the Media Manager role can administer media
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/media_manager"
    Then the media_manager role should have permission to "administer media"
