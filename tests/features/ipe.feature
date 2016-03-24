Feature: In-Place Editor

  @api @javascript
  Scenario: Use the IPE on a Landing Page
    Given I am logged in as a user with the "administrator" role
      And "landing_page" content:
        | title |
        | Test  |
      When I go to "/admin/content"
        Then I should see "Test"
      When I go to "/content/test"
        Then I should see the heading "Test"
          And I should not see "Customize this page"
          And I should not see "Change layout" 
