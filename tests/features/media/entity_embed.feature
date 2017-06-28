@lightning @api @media @javascript
Feature: Embedding entities in a WYSIWYG editor

  Scenario: Embedded images use the Media Image display plugin by default
    Given I am logged in as a user with the page_creator,media_creator roles
    And a random image named "Foobar" with alt text "I am the greetest"
    When I visit "/node/add/page"
    And I open the media browser
    And I select item 1
    And I submit the entity browser
    Then I should see a "form.entity-embed-dialog" element
    And the "Display as" field should contain "media_image"
    # Assert that the default alt text is whatever is in the media item.
    And the "Alternate text" field should contain "I am the greetest"
    # There are two "Title" fields on the page once we reach this assertion --
    # the node title, and the image's title attribute. We need to specify the
    # actual name of the field or Mink will get confused.
    And the "attributes[title]" field should contain "Foobar"
    And I queue the latest media entity for deletion

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

  Scenario: Embedding an image with embed-specific alt text and image style
    Given I am logged in as a user with the page_creator,media_creator roles
    And a random image
    When I visit "/node/add/page"
    And I open the media browser
    And I select item 1
    And I submit the entity browser
    And I select "medium" from "Image style"
    And I enter "Behold my image of randomness" for "Alternate text"
    # There are two "Title" fields on the page at this point -- the node title,
    # and the image's title attribute. We need to specify the actual name of
    # the field or Mink will get confused.
    And I enter "Ye gods!" for "attributes[title]"
    # We can't simply use the "I press Embed" step, because Drupal's dialog box
    # implementation wraps the text of the buttons in <span> tags, which trips
    # that step up.
    And I click the "body > .ui-dialog .ui-dialog-buttonpane button.button--primary" element
    And I wait for AJAX to finish
    And I enter "Foobar" for "Title"
    And I press "Save"
    Then the response should contain "Behold my image of randomness"
    And the response should contain "Ye gods!"
    And I queue the latest media entity for deletion
    And I queue the latest node entity for deletion
