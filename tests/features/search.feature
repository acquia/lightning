# Errors are logged during these scenarios due to dumb stuff in
# multiversion_views_post_execute().
@lightning @api @errors
Feature: Site search

  Scenario: Unpublished content does not appear in search results
    Given I am an anonymous user
    And page content:
      | title    | moderation_state | body                                                                   |
      | Zombie 1 | draft            | Zombie ipsum reversus ab viral inferno, nam rick grimes malum cerebro. |
      | Zombie 2 | needs_review     | De carne lumbering animata corpora quaeritis.                          |
      | Zombie 3 | published        | Summus brains sit, morbo vel maleficia?                              |
    When I visit "/search"
    And I enter "zombie" for "Keywords"
    And I press "Search"
    Then I should not see "Zombie 1"
    And I should not see "Zombie 2"
    And I should see "Zombie 3"
