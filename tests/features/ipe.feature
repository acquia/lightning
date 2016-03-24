Feature: In-Place Editor with FPP

  @api @javascript
  Scenario: Use the IPE to build landing page(s) with FPP.
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
