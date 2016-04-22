@api @lightning @media @javascript
Feature: Media library CKEditor widget

  Scenario: Opening the media library
    Given I am logged in as a user with the page_creator role
    When I go to "node/add/page"
    And I open the CKEditor media widget
    Then I should see a dialog box entitled "Media Library"

  Scenario: Filtering the media library by media type
    Given I am logged in as a user with the page_creator role
    And video media from embed code:
      """
      https://www.youtube.com/watch?v=MTRbm570AHk
      """
    When I go to "/node/add/page"
    And I open the CKEditor media widget
    And I filter the media library by type "image"
    Then I should see "There are no items to display."

  Scenario: Displaying the entity embed dialog box when placing a media entity
    Given I am logged in as a user with the page_creator role
    And video media from embed code:
      """
      https://www.youtube.com/watch?v=sGUNPMPrxvA
      """
    When I go to "/node/add/page"
    And I open the CKEditor media widget
    And I select the 1st asset in the media library
    And I press "Place"
    And I wait for AJAX to finish
    Then I should see the Entity Embed form
