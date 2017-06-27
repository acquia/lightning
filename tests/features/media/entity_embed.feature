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

  @test_module
  Scenario: Embedding an image with embed-specific alt text and image style
    Given I am logged in as a user with the page_creator,media_creator roles
    And a random image
    When I visit "/node/add/page"
    And I open the media browser
    And I select item 1
    And I submit the entity browser
    And I select "medium" from "Image style"
    And I enter "Behold my image of randomness" for "Alternate text"
    # We can't simply use the "I press Embed" step, because Drupal's dialog box
    # implementation wraps the text of the buttons in <span> tags, which trips
    # that step up.
    And I click the "body > .ui-dialog .ui-dialog-buttonpane button.button--primary" element
    And I wait for AJAX to finish
    And I enter "Foobar" for "Title"
    And I press "Save"
    Then the response should contain "Behold my image of randomness"
