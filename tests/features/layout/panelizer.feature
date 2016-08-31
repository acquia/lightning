@lightning @layout @api
Feature: Panelizer

  Scenario: Panelizer is enabled for landing pages
    Given I am logged in as a user with the landing_page_creator role
    And landing_page content:
      | title  | path    |
      | Foobar | /foobar |
    When I visit "/foobar"
    Then I should see a "#panels-ipe-content" element
    And I should not see a ".field--name-uid" element
    And I should not see a ".field--name-created" element

  @javascript
  Scenario: One-off changes can be made to Landing Pages using the IPE out of the box.
    Given I am logged in as a user with the "access panels in-place editing,administer panelizer node landing_page content,edit any landing_page content,view any unpublished content,use draft_draft transition,view latest version,access user profiles" permissions
    And landing_page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I place the "views_block:who_s_online-who_s_online_block" block from the "Lists (Views)" category
    # Click IPE Save
    And I save the layout
    And I visit "/foobar"
    Then I should see a "views_block:who_s_online-who_s_online_block" block

  @javascript
  Scenario: Quick-editing custom blocks in an IPE layout
    Given I am logged in as a user with the administrator role
    And landing_page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    And block_content entities:
      | type  | info               | body    | uuid                  |
      | basic | Here be dragons... | RAWWWR! | test--here-be-dragons |
    When I visit "/foobar"
    And I place the "block_content:test--here-be-dragons" block from the "Custom" category
    And I save the layout
    And I reload the page
    And I wait 5 seconds
    Then I should see a "block_content:test--here-be-dragons" block with a "quickedit" contextual link

  @javascript
  Scenario: Editing layouts does not affect other layouts if the user has not saved the edited layout as default
    Given I am logged in as a user with the administrator role
    And landing_page content:
      | title   | path     | moderation_state |
      | Layout1 | /layout1 | draft            |
      | Layout2 | /layout2 | draft            |
    When I visit "/layout1"
    And I place the "views_block:who_s_online-who_s_online_block" block from the "Lists (Views)" category
    # And visit the second landing page without saving the layout changes to the first
    And I visit "/layout2"
    # I should not see the block placed by the first landing page
    Then I should not see a "views_block:who_s_online-who_s_online_block" block
