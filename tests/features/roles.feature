@lightning @api @beta4
Feature: Responsibility-based user roles

  Scenario: Content-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    And I should see "Content Creator"
    And I should see "Content Manager"

  Scenario: Users with the Content Creator role can create and edit Page content
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/content_creator"
    Then the content_creator role should have permission to:
    """
    view own unpublished content
    create page content
    delete own page content
    edit own page content
    view page revisions
    create url aliases
    """

  Scenario: Users with the Content Manager role have unfettered access to all content
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/permissions/content_manager"
    Then the content_manager role should have permission to:
    """
    access content overview
    administer nodes
    bypass node access
    delete all revisions
    revert all revisions
    view all revisions
    """
