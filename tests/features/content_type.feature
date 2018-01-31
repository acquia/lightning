@lightning @core @api
Feature: Lightning Content Types
  Makes sure that the article content type was created during installation.

  @page @javascript @260b6d63
  Scenario: Ensure that the WYSIWYG editor is present.
    Given I am logged in as a user with the administrator role
    When I visit "node/add/page"
    Then CKEditor "edit-body-0-value" should exist

  @4908d1bd
  Scenario: Ensure the roles configuration form works
    Given I am logged in as a user with the administrator role
    When I visit "/admin/config/system/lightning/roles"
    And I uncheck the box "content_roles[reviewer]"
    And I press "Save configuration"
    Then the response status code should be 200
    And I check the box "content_roles[reviewer]"
    And I press "Save configuration"
