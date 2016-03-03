@api @lightning @media @javascript
Feature: Media library CKEditor widget

  Scenario: Opening the media library
    Given I am logged in as a user with the "administrator" role
    When I go to "node/add/page"
    And I wait for AJAX to finish
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    And I wait for AJAX to finish
    Then I should see "Media Library"

  Scenario: Filtering the media library by media type
    Given I am logged in as a user with the "administrator" role
    When I go to "/node/add/page"
    And I wait for AJAX to finish
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    And I wait for AJAX to finish
    And I select "image" from "lightning-media-bundle"
    And I wait for AJAX to finish
    Then I should see "There are no items to display."

  Scenario: Setting parameters of an embedded media entity
    Given I am logged in as a user with the "administrator" role
    When I go to "/node/add/page"
    And I wait for AJAX to finish
    And I execute the "media_library" command in CKEditor "edit-body-0-value"
    And I wait for AJAX to finish
    And I click the ".media-library .library ul.collection-view li:nth-child(2)" element
    And I press "Place"
    And I wait for AJAX to finish
    Then I should see a "form.entity-embed-dialog" element
    # The Back button should be hidden.
    And I should not see a "form.entity-embed-dialog input[type='submit'][value='Back']" element
    # The display plugin selection should be hidden.
    And I should not see a "form.entity-embed-dialog select[name='attributes[data-entity-embed-display]']" element
