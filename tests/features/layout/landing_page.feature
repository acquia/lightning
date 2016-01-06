@lightning @lightning_media
  Feature: A simple form to create landing pages.

    @api
    Scenario: Creating a landing page
      Given I am logged in as a user with the "administer pages" permission
      When I visit "/admin/structure/landing-page"
      And I enter "Foobar" for "Title"
      And I enter "/foobar" for "Path"
      And I select "Single column" from "Layout"
      And I press the "Create" button
      Then I should be on "/foobar"
      And the response status code should be 200
      And I should see "Foobar"

    @api
    Scenario: Trying to create a landing page twice
      Given I am logged in as a user with the "administer pages" permission
      When I visit "/admin/structure/landing-page"
      And I enter "Foobar" for "Title"
      And I enter "/foobar" for "Path"
      And I select "Single column" from "Layout"
      And I press the "Create" button
      Then I should see "A landing page already exists at /foobar."
