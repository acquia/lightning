@api @lightning
Feature: Contact form(s)

  Scenario: Accessing the site-wide contact form anonymously
    Given I am an anonymous user
    When I visit "/contact"
    Then I should see a "Your name" field
    And I should see a "Your email address" field
    And I should see a "Subject" field
    And I should see a "Message" field

  Scenario: Accessing the site-wide contact form as an authenticated user
    Given I am logged in as a user with the authenticated role
    When I visit "/contact"
    Then I should not see a "Your name" field
    And I should not see a "Your email address" field
    And I should see a "Subject" field
    And I should see a "Message" field
