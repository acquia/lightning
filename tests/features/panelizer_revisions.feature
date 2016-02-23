@lightning @layout @workflow @api
Feature: Showing the Panels IPE interface on the latest content revision only

  Scenario: Showing the Panels IPE interface on the latest content revision only
    Given I am logged in as a user with the "administer nodes,bypass node access,use draft_draft transition,administer node display,access panels in-place editing,administer panelizer node page content,view any unpublished content,view latest version" permissions
    And I have panelized the page node type
    And page content:
      | title  | body                          | path    | moderation_state |
      | Foobar | This is the initial revision! | /foobar | draft            |
    When I visit "/foobar"
    And I click "Edit draft"
    And I enter "This is the second revision!" for "body[0][value]"
    And I press "Save and Create New Draft"
    And I click "Revisions"
    And I click the "td.revision-current:nth-child(1) a" element
    Then I should see a "#panels-ipe-content" element
    And I click "Revisions"
    And I click the "main tr:nth-child(2) td:nth-child(1) a" element
    And I should not see a "#panels-ipe-content" element
    And I cleanup the "/foobar" alias
    And I unpanelize the page node type
