Feature: Panel layouts
  Scenario: Header
    Given I am on "/"
    Then I should see "Lightning" in the "Header" region
  Scenario: Content
    Given I am on "/"
    Then I should see "Lightning" in the "Content" region
      And I should see "A fast and feature-rich Drupal distributon" in the "Content" region
  Scenario: Footer
    Given I am on "/"
    Then I should see "© 2016 Lightning All rights reserved." in the "Footer" region

  @api
  Scenario: Main page content
    Given I am on "/"
      And I am logged in as a user with the "authenticated user" role
    Then I should see "My account" in the "Header" region
      And I should see "Log out" in the "Header" region
