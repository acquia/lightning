@lightning @api @layout
Feature: Responsibility-based user roles for editing and managing layouts

  @beta5
  Scenario: Layout-related user roles should exist
    Given I am logged in as a user with the "administer permissions" permission
    When I visit "/admin/people/roles"
    Then I should see "Landing Page Creator"
    And I should see "Landing Page Reviewer"
