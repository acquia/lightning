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
    And I select "image" from "lightning-media-bundle"
    And I wait for AJAX to finish
    Then I should see "There are no items to display."

  Scenario: Displaying the entity embed dialog box when placing a media entity
    Given I am logged in as a user with the page_creator role
    And video media from embed code:
      """
      https://www.youtube.com/watch?v=sGUNPMPrxvA
      """
    When I go to "/node/add/page"
    And I open the CKEditor media widget
    And I click the ".media-library .library ul.collection-view li:first-child" element
    And I press "Place"
    And I wait for AJAX to finish
    Then I should see a "form.entity-embed-dialog" element
    # The Back button should be hidden.
    And I should not see a "form.entity-embed-dialog input[type='submit'][value='Back']" element
    # The display plugin selection should be hidden.
    And I should not see a "form.entity-embed-dialog select[name='attributes[data-entity-embed-display]']" element
