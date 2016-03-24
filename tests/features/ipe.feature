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
          And I should see "Moderate Landing Page"
          And I should see "Published"
      When I follow "Moderate Landing Page"
        Then I should see "View published"
          And I should see "New draft"
          And I should see "Moderate"
          And I should see "Customize display"
      When I follow "New draft"
        Then I should see "Status: New draft of live content."
      When I press "Save as draft"
        Then I should see "Landing Page Test has been updated."
          And I should see "Customize this page"
          And I should see "Change layout"
      When I follow "Customize this page"
        Then I should see "Save as custom"
          And I should see "Cancel"
