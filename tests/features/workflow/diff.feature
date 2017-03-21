@lightning @workflow @api
Feature: Diffing different revisions of content

  @5b4ba63e
  Scenario: Diffing two node revisions
    Given I am logged in as a user with the page_creator role
    And page content:
      | title       | body           | path         | moderation_state |
      | Pastafazoul | First revision | /pastafazoul | draft            |
    When I visit "/pastafazoul"
    And I click "Edit draft"
    And I enter "Second revision" for "body[0][value]"
    And I press "Save"
    And I click "Edit draft"
    And I enter "Third revision" for "body[0][value]"
    And I press "Save"
    And I compare the 1st and 2nd revisions
    Then I should see "Changes to Pastafazoul"
