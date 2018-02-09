@lightning @core @api
Feature: Lightning Content Types
  Makes sure that the article content type was created during installation.

  @page @javascript @260b6d63
  Scenario: Ensure that the WYSIWYG editor is present.
    Given I am logged in as a user with the administrator role
    When I visit "node/add/page"
    Then CKEditor "edit-body-0-value" should exist
