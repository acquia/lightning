@lightning @media @api @javascript @errors
Feature: Uploading media assets through the media browser

  @image @document @with-module:lightning_test @1f81e59b
  Scenario Outline: Uploading a file from within the media browser
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I upload "<file>"
    And I enter "<title>" for "Media name"
    And I press "Place"
    And I visit "/admin/content/media"
    Then I should see "<title>"

    Examples:
      | file     | title       |
      | test.jpg | Foobazzz    |
      | test.pdf | A test file |

  @b34126c1
  Scenario: The upload widget should require a file
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I click "Upload"
    And I press "Place"
    Then I should see the error message "You must upload a file."

  @image @with-module:lightning_test @2f72f4a4
  Scenario: The upload widget validates file size
    Given I am logged in as a user with the media_creator,page_creator roles
    When I visit "/node/add/page"
    And I open the "Lightweight Image" image browser
    And I click "Upload"
    And I attach the file "test.jpg" to "input_file"
    And I wait for AJAX to finish
    # This is a weak-sauce assertion but I can't tell exactly what the error
    # message will say.
    Then I should see a ".messages [role='alert']" element
    And I should see an "input.form-file.error" element

  @security @57537d2b
  Scenario: The upload widget rejects files with unrecognized extensions
    Given I am logged in as a user with the media_creator role
    When I visit "/entity-browser/iframe/media_browser"
    And I click "Upload"
    And I attach the file "test.php" to "input_file"
    And I wait for AJAX to finish
    Then I should see the error message containing "Only files with the following extensions are allowed:"

  @security @627aeb22
  Scenario: Upload widget will not allow the user to create media of bundles to which they do not have access
    Given I am logged in as a user with the "access media_browser entity browser pages" permission
    When I visit "/entity-browser/iframe/media_browser"
    And I click "Upload"
    And I attach the file "test.php" to "input_file"
    And I wait for AJAX to finish
    Then the "#entity" element should be empty

  @with-module:upload_bundles_test @ced013a5
  Scenario: The upload widget should respect media bundles allowed by the field
    Given I am logged in as a user with the "page_creator,media_creator,media_manager" roles
    When I visit "/node/add/page"
    And I switch to the "entity_browser_iframe_media_browser" frame
    And I upload "test.jpg"
    And I enter "Z Image Test" for "Media name"
    And I submit the entity browser
    Then there should be 1 z_image media entity
    And there should be 0 image media entities
