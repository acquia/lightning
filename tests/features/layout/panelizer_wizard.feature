@lightning @layout @api @errors
Feature: Panelizer Wizard

  @landing-page @8ce434bd
  Scenario: Full content landing page layout has the proper Content context
    Given I am logged in as a user with the layout_manager role
    # Initialize the tempstore
    When I visit "/admin/structure/panelizer/edit/node__landing_page__full__default"
    # Then view the list of available contexts
    And I visit "/admin/structure/panels/panelizer.wizard/node__landing_page__full__default/select_block"
    Then I should see "Authored by"

  @landing-page @javascript @0e995113
  Scenario: Saving a panelized entity should not affect blocks placed via IPE
    Given I am logged in as a user with the "access panels in-place editing,administer panelizer node landing_page content,edit any landing_page content,view any unpublished content,use draft_draft transition,view latest version,access user profiles,set panelizer default" permissions
    And landing_page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    And I place the "views_block:who_s_online-who_s_online_block" block from the "Lists (Views)" category
    # Click IPE Save
    And I save the layout
    And I click "Edit draft"
    And I press "Save"
    Then I should see a "views_block:who_s_online-who_s_online_block" block

  @landing-page @8901c0df
  Scenario: The default layout switcher is available on entity edit forms for each view mode that has the "Allow panelizer default choice" optioned enabled, and only those view modes.
    Given I am logged in as a user with the "landing_page_creator,layout_manager" roles
    And I have customized the search_result view mode of the landing_page content type
    When I visit "/admin/structure/types/manage/landing_page/display/search_result"
    And I check "panelizer[enable]"
    And I check "panelizer[custom]"
    And I check "panelizer[allow]"
    And I press "Save"
    And I visit "/node/add/landing_page"
    Then I should see a "Full content" field
    And I should see a "Search result highlighting input" field
    And I uncustomize the search_result view mode of the landing_page content type
    And I visit "/node/add/landing_page"
    And I should not see a "Search result highlighting input" field

  @landing-page @javascript @7917f3ad
  Scenario: Switch between defined layouts.
    Given users:
      | name | mail          | roles                               |
      | Foo  | foo@localhost | landing_page_creator,layout_manager |
    And I am logged in as Foo
    And I visit "/admin/structure/panelizer/edit/node__landing_page__full__two_column/content"
    And I place the "Authored by" block into the first panelizer region
    And I press "Update and save"
    And landing_page content:
      | title  | path    | moderation_state | author |
      | Foobar | /foobar | draft            | Foo    |
    When I visit "/foobar"
    And I click "Edit draft"
    And I select "Two Column" from "Full content"
    And press "Save"
    Then I should see "Authored by"
    And I click "Edit draft"
    And I select "Single Column" from "Full content"
    And press "Save"
    And I should not see "Authored by"
    And I visit "/admin/structure/panelizer/edit/node__landing_page__full__two_column/content"
    And I remove the "Authored by" block from the first panelizer region

  @landing-page @javascript @415e9f49
  Scenario: The default layout select list should be disabled on entities whose layout has been customized via the IPE.
    Given users:
      | name | mail          | roles                               |
      | Foo  | foo@localhost | layout_manager,landing_page_creator |
    And I am logged in as Foo
    And landing_page content:
      | title  | path    | moderation_state | author |
      | Foobar | /foobar | draft            | Foo    |
    When I visit "/foobar"
    And I place the "views_block:who_s_online-who_s_online_block" block from the "Lists (Views)" category
    And I save the layout
    And I click "Edit draft"
    Then the "Full content" field should be disabled

  @landing-page @javascript @6025f79d
  Scenario:  Block placement on non-default displays are preserved after re-saving the entity.
    Given users:
      | name | mail          | roles                               |
      | Foo  | foo@localhost | layout_manager,landing_page_creator |
    And I am logged in as Foo
    And landing_page content:
      | title  | path    | moderation_state | author |
      | Foobar | /foobar | draft            | Foo    |
    And block_content entities:
      | type  | info               | body    | uuid                  |
      | basic | Here be dragons... | RAWWWR! | test--here-be-dragons |
    When I visit "/foobar"
    And I click "Edit draft"
    And I select "two_column" from "Full content"
    And I press "Save"
    And I place the "block_content:test--here-be-dragons" block from the "Custom" category
    And I save the layout
    And I click "Edit draft"
    And I press "Save"
    Then I should see a "block_content:test--here-be-dragons" block

  @landing-page @javascript @20e106df
  Scenario: Create a new layout using the Panelizer Wizard
    Given I am logged in as a user with the "layout_manager" role
    When I go to "/admin/structure/panelizer/add/node/landing_page/full"
    And I press "Next"
    And I enter "Foo" for "Wizard name"
    And I enter "foo" for "Machine-readable name"
    And I press "Next"
    And I press "Next"
    And I press "Next"
    And I enter "[node:title]" for "Page title"
    And I place the "Authored by" block into the "content" panelizer region
    And I press "Finish"
    And I press "Cancel"
    And I should be on "/admin/structure/types/manage/landing_page/display/full"
    Then I should see "Foo"
    And I go to "/admin/structure/panelizer/delete/node__landing_page__full__foo"
    And I press "Confirm"
