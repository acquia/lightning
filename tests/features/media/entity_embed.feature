@lightning @api @media @javascript
Feature: Embedding entities in a WYSIWYG editor

  @test_module
  Scenario: Embedded images use the Media Image display plugin by default
    Given I am logged in as a user with the page_creator,media_creator roles
    And a random image
    When I visit "/node/add/page"
    And I open the media browser
    And I select item 1
    And I submit the entity browser
    Then I should see a "form.entity-embed-dialog" element
    And the "Display as" field should contain "media_image"

  Scenario: Embedded videos use the Embedded display plugin by default
    Given I am logged in as a user with the page_creator,media_creator roles
    And video media from embed code:
      """
      https://www.youtube.com/watch?v=N2_HkWs7OM0
      """
    When I visit "/node/add/page"
    And I open the media browser
    And I select item 1
    And I submit the entity browser
    Then I should see a "form.entity-embed-dialog" element
    And the "Display as" field should contain "view_mode:media.embedded"
