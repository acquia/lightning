@lightning @core @media @api
Feature: Responsibility-based user roles for creating and managing media assets

  @beta5 @80dd53a1
  Scenario: Media-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    Then I should see "Media Creator"
    And I should see "Media Manager"

  @beta5 @68d467b4
  Scenario: Content creators have access to the rich_text input format and entity browsers
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions"
    Then the page_creator role should have permission to:
      """
      use text format rich_text
      access media_browser entity browser pages
      access image_browser entity browser pages
      """

  @video @beta5 @d2a26938
  Scenario: Creating media as a media creator
    Given I am logged in as a user with the media_creator role
    And media entities:
      | bundle | name          | field_media_video_embed_field               | status |
      | video  | Steven Wright | https://www.youtube.com/watch?v=9Mz3EWJGGH0 | 1      |
    When I visit "/admin/content/media"
    And I click "Steven Wright"
    Then the response status code should be 200
    And I should see the link "Edit"
    And I should see the link "Delete"

  @video @beta5 @e21b1343
  Scenario: Users with the Media Manager role can edit media created by any other user
    Given I am logged in as a user with the media_creator role
    And media entities:
      | bundle | name           | field_media_video_embed_field               | status |
      | video  | FunFunFunction | https://www.youtube.com/watch?v=UD2dZw9iHCc | 1      |
    And users:
      | name | roles         | pass |
      | foo  | media_manager | foo  |
    When I visit "/user/logout"
    And I visit "/user/login"
    And I enter "foo" for "Username"
    And I enter "foo" for "Password"
    And I press "Log in"
    And I visit "/admin/content/media"
    And I click "FunFunFunction"
    Then I should see "Edit"
    And I should see "Delete"
