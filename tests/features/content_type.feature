@api @lightning
Feature: Lightning Content Types
  Makes sure that the article content type was created during installation.

  Scenario: Make sure that the content types provided by Lightning at installation are present.
    Given I am logged in as a user with the administrator role
    When I visit "/node/add"
    Then I should see "Basic page"
    And I should see "Landing Page"

  @javascript
  Scenario: Ensure that the WYSIWYG editor is present.
    Given I am logged in as a user with the administrator role
    When I visit "node/add/page"
    Then CKEditor "edit-body-0-value" should exist

  Scenario: Ensure that meta tag fields are present.
    Given I am logged in as a user with the administrator role
    When I visit "node/add/page"
    Then I should see a "field_meta_tags[0][basic][title]" field
    And I should see a "field_meta_tags[0][basic][description]" field

  Scenario: The basic block content type should have a body field.
    Given I am logged in as a user with the "administrator" role
    When I visit "/block/add"
    Then I should see a "Body" element

  Scenario: Ensure the roles configuration form works
    Given I am logged in as a user with the administrator role
    When I visit "/admin/config/system/lightning"
    And I uncheck the box "content_roles[reviewer]"
    And I press "Save configuration"
    Then the response status code should be 200
    And I check the box "content_roles[reviewer]"
    And I press "Save configuration"

  Scenario: Automatically creating creator and reviewer roles for a content type
    Given I am logged in as a user with the administrator role
    And node_type entities:
      | type | name |
      | foo  | foo  |
    And I visit "/admin/people/roles"
    Then I should see "foo Creator"
    And I should see "foo Reviewer"

  Scenario: Automatically deleting creator and manager roles for a content type
    Given I am logged in as a user with the administrator role
    And node_type entities:
      | type | name |
      | foo  | foo  |
    When I visit "/admin/structure/types/manage/foo/delete"
    And I press "Delete"
    And I visit "/admin/people/roles"
    Then I should not see "foo Creator"
    And I should not see "foo Reviewer"

  Scenario: Removing access to workflow actions that do not make sense with moderated content
    Given I am logged in as a user with the administrator role
    And page content:
      | title |
      | Foo   |
      | Bar   |
      | Baz   |
    When I visit "/admin/content"
    Then "Action" should not have a "node_publish_action" option
    And "Action" should not have a "node_unpublish_action" option

  Scenario: Describing a view mode
    Given I am logged in as a user with the "access administration pages,administer display modes,administer node display" permissions
    When I visit "/admin/structure/display-modes/view/add/node"
    And I enter "Foobaz" for "Name"
    And I enter "foobaz" for "id"
    And I enter "Behold my glorious view mode" for "Description"
    And I press "Save"
    And I visit "/admin/structure/types/manage/page/display"
    And I check the box "display_modes_custom[foobaz]"
    And I press "Save"
    And I visit "/admin/structure/types/manage/page/display/foobaz"
    Then I should see "Behold my glorious view mode"
    # Clean up.
    And I visit "/admin/structure/display-modes/view/manage/node.foobaz/delete"
    And I press "Delete"
