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
