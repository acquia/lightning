Feature: Anonymous user login
  Scenario: Admin user is able to login
    Given I am on "/user"
    When I fill in "admin" for "edit-name"
      And I fill in "admin" for "edit-pass"
      And I press "Log in"
    Then I should see "Log out"

  Scenario: User can request a new password if it has been lost
    Given I am on "/user/password"
    Then I should see "Request new password"
    When I fill in "admin" for "edit-name"
      And press "E-mail new password"
    Then I should see "Further instructions have been sent to your e-mail address."
      And I should see "Log in"

  @drush
  Scenario: User can change password after using one-time-login-url
    Given I log in with the One Time Login Url
    Then I should see the heading "Reset password"
    And I should see "This login can be used only once."
    When I press the "Log in" button
    Then I should see "You have just used your one-time login link. It is no longer necessary to use this link to log in. Please change your password."
    When I fill in "mycoolpassword" for "Password"
    And I fill in "mycoolpassword" for "Confirm password"
    And I press "Save"
    Then I should see "The changes have been saved."
    When I click "Log out"
