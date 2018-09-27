@lightning @api
Feature: Moderation Dashboard.

  @javascript @ea966cba
  Scenario: Admin can use dashboard.
    Given I am logged in as a user with the administrator role
    Then I should see a "views_block:content_moderation_dashboard_in_review-block_1" block
    And I should see a "views_block:content_moderation_dashboard_in_review-block_2" block
    And I should see a "moderation_dashboard_activity" block
    And I should see a "views_block:moderation_dashboard_recently_created-block_1" block
    And I should see a "views_block:content_moderation_dashboard_in_review-block_3" block
    And I should see a "views_block:moderation_dashboard_recent_changes-block_1" block
    And I should see a "views_block:moderation_dashboard_recent_changes-block_2" block
    And I should see a "views_block:moderation_dashboard_recently_created-block_2" block
    And the url should match "user/\d+/moderation/dashboard"
    And I click the "#toolbar-item-user" element
    Then I should see the link "Moderation Dashboard"
