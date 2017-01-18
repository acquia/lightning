@lightning @api @errors
Feature: Site search

  # Errors are logged during this scenario due to dumb stuff in
  # multiversion_views_post_execute().
  Scenario: Searching the site for content
    Given I am an anonymous user
    And page content:
      | title    | moderation_state | body                                                                   |
      | Zombie 1 | published        | Zombie ipsum reversus ab viral inferno, nam rick grimes malum cerebro. |
      | Zombie 2 | published        | De carne lumbering animata corpora quaeritis.                          |
      | Zombie 3 | published        | Summus brains sit​​, morbo vel maleficia?                              |
    When I visit "/search"
    And I enter "ipsum" for "Keywords"
    And I press "Search"
    Then I should see "Zombie 1"
    And I should not see "Zombie 2"
    And I should not see "Zombie 3"
