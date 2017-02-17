@lightning @media @api @javascript @errors
Feature: Creating media assets from within the media browser using embed codes

  @test_module
  Scenario: Creating a YouTube video from within the media browser
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://www.youtube.com/watch?v=zQ1_IbFFbzA"
    And I enter "The Pill Scene" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "The Pill Scene"

  @test_module
  Scenario: Creating a Vimeo video from within the media browser
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://vimeo.com/14782834"
    And I enter "Cache Rules Everything Around Me" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "Cache Rules Everything Around Me"

  @test_module
  Scenario: Creating a tweet from within the media browser
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://twitter.com/webchick/status/672110599497617408"
    And I enter "angie speaks" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "angie speaks"

  @test_module
  Scenario: Creating an Instagram post from within the media browser
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I enter embed code "https://www.instagram.com/p/jAH6MNINJG"
    And I enter "Drupal Does LSD" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "Drupal Does LSD"

  Scenario: Embed code widget should require input
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I click "Create embed"
    And I press "Place"
    Then I should see the error message "You must enter a URL or embed code."

  Scenario: Embed code widget should ensure that input can be matched to a media bundle
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I click "Create embed"
    And I enter "The quick brown fox gets eaten by hungry lions." for "input"
    And I wait for AJAX to finish
    And I press "Place"
    Then I should see the error message "No media types can be matched to this input."

  @security
  Scenario: Embed code widget will not allow the user to create media of bundles to which they do not have access
    Given I am logged in as a user with the "access media_browser entity browser pages" permission
    When I visit "/entity-browser/iframe/media_browser"
    And I click "Create embed"
    And I enter "https://twitter.com/webchick/status/824051274353999872" for "input"
    And I wait for AJAX to finish
    Then the "#entity" element should be empty
