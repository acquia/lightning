@lightning @layout @api
Feature: Panelizer

  Scenario: Panelizer is enabled for landing pages
    Given I am logged in as a user with the "administer nodes,access panels in-place editing,administer panelizer node landing_page content" permissions
    And landing_page content:
      | title  | path    |
      | Foobar | /foobar |
    When I visit "/foobar"
    Then I should see a "#panels-ipe-content" element
    And I should not see a ".field--name-uid" element
    And I should not see a ".field--name-created" element
    And I cleanup the "/foobar" alias

  @javascript
  Scenario: One-off changes can be made to Landing Pages using the IPE out of the box.
    Given I am logged in as a user with the "access panels in-place editing,administer panelizer node landing_page content,edit any landing_page content,view any unpublished content,use draft_draft transition,view latest version,access user profiles" permissions
    And landing_page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    # Click on IPE Place Content
    And I click the "a[title='Place Content']" element
    # And I click the ".ipe-tabs .ipe-tab:nth-child(2) a" element
    And I wait for AJAX to finish
    # Click on IPE Lists (Views)
    And I click the "a[data-category='Lists (Views)']" element
    And I wait for AJAX to finish
    # Add Who's Online Block
    And I click the "a[data-plugin-id='views_block:who_s_online-who_s_online_block']" element
    And I wait for AJAX to finish
    # Click Add on the block form
    And I click the ".ipe-block-plugin-form .js-form-submit" element
    And I wait for AJAX to finish
    # Click IPE Save
    And I click the ".ipe-tabs .ipe-tab:nth-child(4) a" element
    And I wait for AJAX to finish
    And I click "Save as custom"
    And I wait for AJAX to finish
    And I visit "/foobar"
    And I click "View"
    And I click "Latest version"
    Then I should see "There are currently"
    And I cleanup the "/foobar" alias
