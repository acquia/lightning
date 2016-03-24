Feature: In-Place Editor

  @api @javascript
  Scenario: Use the IPE on a Landing Page
    Given I am logged in as a user with the "administrator" role
      And "landing_page" content:
        | title                 |
        | My test landing page  |
    When I go to "/admin/content"
      Then I should see "My test landing page"
    When I follow "My test landing page"
      Then I should see "Customize this page"
        And I should see "Change layout"
    When I follow "Customize this page"
      Then I should see "Save as custom"
        And I should see "Cancel"
