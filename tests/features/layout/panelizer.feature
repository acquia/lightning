@lightning @layout @api
Feature: Panelizer

  Scenario: Panelizer is enabled for landing pages
    Given I am logged in as a user with the landing_page_creator role
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
    Then I should see "There are currently"
    And I cleanup the "/foobar" alias

  @javascript
  Scenario: Quick-editing custom blocks in an IPE layout
    Given I am logged in as a user with the administrator role
    And landing_page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    And block_content entities:
      | type  | info               | body    | uuid                  |
      | basic | Here be dragons... | RAWWWR! | test--here-be-dragons |
    When I visit "/foobar"
    And I click the "a[title='Place Content']" element
    And I wait for AJAX to finish
    And I click the "a.ipe-category[data-category='Custom']" element
    And I wait for AJAX to finish
    And I click the "a[data-plugin-id='block_content:test--here-be-dragons']" element
    And I wait for AJAX to finish
    And I click the ".ipe-block-plugin-form input[type='submit'][value='Add']" element
    And I wait for AJAX to finish
    And I click the "a[title='Save']" element
    And I wait for AJAX to finish
    And I click the "a.panelizer-ipe-save-custom" element
    And I wait for AJAX to finish
    And I reload the page
    Then I should see a "div[data-block-plugin-id='block_content:test--here-be-dragons'] ul.contextual-links li.quickedit" element
    And I cleanup the "/foobar" alias

  @javascript
  Scenario: Editing layouts does not affect other layouts if the user has not saved the edited layout as default
    Given I am logged in as a user with the administrator role
    And landing_page content:
      | title   | path     | moderation_state |
      | Layout1 | /layout1 | draft            |
      | Layout2 | /layout2 | draft            |
    When I visit "/layout1"
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
    # And visit the second landing page without saving the layout changes to the first
    And I visit "/layout2"
    # I should not see the block placed by the first landing page
    Then I should not see "There are currently"
    And I cleanup the "/layout1" alias
    And I cleanup the "/layout2" alias

