@lightning @media @api @javascript @errors
Feature: Media asset browsers for CKEditor and image fields

  @test_module
  Scenario: Uploading an image from within the media browser
    Given I am logged in as a user with the media_manager role
    When I visit "/entity-browser/iframe/media_browser"
    And I upload "test.jpg"
    And I enter "Foobazzz" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "Foobazzz"

  @test_module
  Scenario: Uploading a document from within the media browser
    Given I am logged in as a user with the media_manager role
    When I visit "/entity-browser/iframe/media_browser"
    And I upload "test.pdf"
    And I enter "A test file" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "A test file"

  @test_module
  Scenario: Creating a YouTube video from within the media browser
    Given I am logged in as a user with the media_manager role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://www.youtube.com/watch?v=zQ1_IbFFbzA"
    And I enter "The Pill Scene" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "The Pill Scene"

  @test_module
  Scenario: Creating a Vimeo video from within the media browser
    Given I am logged in as a user with the media_manager role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://vimeo.com/14782834"
    And I enter "Cache Rules Everything Around Me" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "Cache Rules Everything Around Me"

  @test_module
  Scenario: Creating a tweet from within the media browser
    Given I am logged in as a user with the media_manager role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://twitter.com/webchick/status/672110599497617408"
    And I enter "angie speaks" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "angie speaks"

  @test_module
  Scenario: Creating an Instagram post from within the media browser
    Given I am logged in as a user with the media_manager role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://www.instagram.com/p/jAH6MNINJG"
    And I enter "Drupal Does LSD" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "Drupal Does LSD"

  @test_module
  Scenario: Uploading an image through the image browser
    Given I am logged in as a user with the page_creator role
    When I visit "/node/add/page"
    And I open the "Hero Image" image browser
    And I click "Upload"
    And I attach the file "test.jpg" to "File"
    And I wait for AJAX to finish
    And I enter "Behold, a generic logo" for "Media name"
    And I switch to the window
    And I submit the entity browser
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I should not see a "table[drupal-data-selector='edit-image-current'] td.empty" element

  Scenario: Opening the media browser on a pre-existing node
    Given I am logged in as a user with the "page_creator,page_reviewer,media_creator" roles
    And media entities:
      | bundle | name            | embed_code                                                  | status | field_media_in_library |
      | tweet  | Here be dragons | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | 1                      |
    When I visit "/node/add/page"
    And I enter "Blorgzville" for "Title"
    And I open the media browser
    And I select item 1 in the media browser
    And I complete the media browser selection
    And I wait 5 seconds
    And I press "Save"
    And I click "Edit draft"
    And I wait 10 seconds
    And I open the media browser
    And I wait for AJAX to finish
    Then I should see a "form.entity-browser-form" element
    And I queue the latest node entity for deletion

  Scenario: Testing cardinality enforcement in the media browser
    Given I am logged in as a user with the page_creator,media_creator roles
    And media entities:
      | bundle | name          | embed_code                                               | status | field_media_in_library |
      | tweet  | Code Wisdom 1 | https://twitter.com/CodeWisdom/status/707945860936691714 | 1      | 1                      |
      | tweet  | Code Wisdom 2 | https://twitter.com/CodeWisdom/status/826500049760821248 | 1      | 1                      |
      | tweet  | Code Wisdom 3 | https://twitter.com/CodeWisdom/status/826460810121773057 | 1      | 1                      |
    When I visit "/node/add/page"
    And I open the media browser
    # There was a bug where AJAX requests would completely break the selection
    # behavior. So let's make an otherwise pointless AJAX request here to guard
    # against regressions...
    And I enter "Pastafazoul!" for "Keywords"
    And I press "Apply"
    And I wait for AJAX to finish
    And I clear "Keywords"
    And I press "Apply"
    And I wait for AJAX to finish
    And I select item 1 in the media browser
    And I select item 2 in the media browser
    Then 1 element should match "[data-selectable].selected"
    # No choices are ever disabled in a single-cardinality entity browser.
    And nothing should match "[data-selectable].disabled"

  @test_module
  Scenario: Testing cardinality enforcement with a multi-value image field
    Given I am logged in as a user with the page_creator,media_creator roles
    And 4 random images
    When I visit "/node/add/page"
    And I open the "Multi-Image" image browser
    And I select item 2
    And I select item 3
    And I switch to the window
    And I submit the entity browser
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I open the "Multi-Image" image browser
    And I select item 1
    Then at least 3 elements should match "[data-selectable].disabled"
