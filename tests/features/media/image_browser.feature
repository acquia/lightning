@lightning @media @image @api @javascript @errors @test_module
Feature: An entity browser for image fields

  @1bbf2fa6
  Scenario: Uploading an image through the image browser
    Given I am logged in as a user with the page_creator,media_creator roles
    When I visit "/node/add/page"
    And I open the "Hero Image" image browser
    And I click "Upload"
    And I attach the file "test.jpg" to "File"
    And I wait for AJAX to finish
    And I enter "Behold, a generic logo" for "Media name"
    And I submit the entity browser
    Then I should not see a "table[drupal-data-selector='edit-image-current'] td.empty" element

  @1bbf34d8
  Scenario: Testing cardinality enforcement with a multi-value image field
    Given I am logged in as a user with the page_creator,media_creator roles
    And 4 random images
    When I visit "/node/add/page"
    And I open the "Multi-Image" image browser
    And I select item 2
    And I select item 3
    And I submit the entity browser
    And I open the "Multi-Image" image browser
    And I select item 1
    Then at least 3 elements should match "[data-selectable].disabled"

  @1bbf35e6
  Scenario: Testing an image browser with unlimited cardinality
    Given I am logged in as a user with the page_creator,media_creator roles
    And 4 random images
    When I visit "/node/add/page"
    And I open the "Unlimited Images" image browser
    And I select item 1
    And I select item 2
    And I select item 3
    And I submit the entity browser
    And I open the "Unlimited Images" image browser
    And I select item 4
    Then nothing should match "[data-selectable].disabled"
