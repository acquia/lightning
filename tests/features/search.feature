@lightning @core @search @api @errors
Feature: Site search

  @layout @landing-page @javascript @6aa9edbb
  Scenario: Indexing and searching for landing pages
    Given I am logged in as a user with the "landing_page_creator, landing_page_reviewer, layout_manager" roles
    And landing_page content:
      | title  | path    | moderation_state | body                                                    |
      | Foobar | /foobar | draft            | In which my landing page is described in a flowery way. |
    And block_content entities:
      | type  | info    | body             | uuid    |
      | basic | Dragons | Here be dragons. | dragons |
    When I visit "/foobar"
    And I place the "block_content:dragons" block from the "Custom" category
    And I save the layout
    And I visit the edit form
    And I select "Published" from "moderation_state[0][state]"
    And I press "Save"
    And I am an anonymous user
    And I visit "/search"
    And I enter "dragons" for "Keywords"
    And I press "Search"
    Then I should see "Foobar"
    And I should see "In which my landing page is described in a flowery way."
