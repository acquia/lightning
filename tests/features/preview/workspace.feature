@lightning @preview @api
Feature: Workspaces

  @d1a9bf4d
  Scenario: Locking a workspace by publishing it
    Given I am logged in as a user with the administrator role
    And I set the "Stage" workspace to the "Published" moderation state
    And I switch to the "Stage" workspace
    And I go to "/node/add/page"
    Then I should not see the button "Save"
    And I set the "Stage" workspace to the "Draft" moderation state

  @errors @615bccb8
  Scenario: Configuration entities are unconditionally locked in every workspace except the Live workspace
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/workspace/2/activate"
    And I press "Activate"
    And I visit "/admin/structure/block"
    And I press "Save blocks"
    Then the response status code should be 500
    And I should see "Configuration can only be modified in the Live workspace"

  @errors @8506c8b7
  Scenario: Configuration entity form routes cannot be accessed in any workspace except the Live workspace
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/workspace/2/activate"
    And I press "Activate"
    And I visit "/admin/config/content/formats"
    Then I should not see an "Add text format" link

  @3828aede
  Scenario: Configuration entity forms protected by standard permissions cannot be accessed in any workspace except the Live workspace
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/workspace/2/activate"
    And I press "Activate"
    And I visit "/admin/structure/workbench-moderation/states/draft"
    Then I should see "Configuration can only be modified in the Live workspace"
    And I should not see the button "Save"

  @f2f3f728
  Scenario: Workspaces are allowed to be in the Draft, Needs Review, and Published states, but not Archived
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/workspace/types/basic/edit/moderation"
    And the "Draft" checkbox should be checked
    And the "Needs Review" checkbox should be checked
    And the "Published" checkbox should be checked
    Then the "Archived" checkbox should not be checked

  @9c7ede8a
  Scenario: Moderation states available to Workspace entities can be marked as Locked and others cannot
    Given I am logged in as a user with the administrator role
    When I visit "/admin/structure/workbench-moderation/states/needs_review"
    And I should see "Lock workspaces in this state"
    And I visit "/admin/structure/workbench-moderation/states/archived"
    Then I should not see "Lock workspaces in this state"

  @bfe4fe04
  Scenario: The Needs Review and Published states that ship with Lightning are Locked but Draft is not
    Given I am logged in as a user with the administrator role
    And the "needs_review" state should be locked
    And the "published" state should be locked
    Then the "draft" state should not be locked

  @87d0dfad
  Scenario: The Live workspace that ships with Lightning is live
    Given I am logged in as a user with the administrator role
    When I visit "/node/add/page"
    And I fill in "WPS Test Title" for "Title"
    And I select "Published" from "Moderation state"
    And I fill in "/wps-test" for "URL alias"
    And I press "Save"
    And I queue the latest "node" entity for deletion
    And I visit "/user/logout"
    And I visit "/wps-test"
    Then I should see "WPS Test Title"

  @cleanup @59f7fecf
  Scenario: Custom paths are prefixed when created on non-live workspaces
    Given I am logged in as a user with the administrator role
    And I switch to the "Stage" workspace
    And I visit "/node/add/page"
    And I fill in "WPS Alias Test 1" for "Title"
    And I fill in "/wps-alias-test-1" for "URL alias"
    And I press "Save"
    Then I should be on "/stage/wps-alias-test-1"
    # Manual cleanup
    And I click "Delete"
    And I press "Delete"

  @cleanup @e6abc176
  Scenario: The Stage workspace that ships with Lightning is not the Live workspace
    Given I am logged in as a user with the administrator role
    When I switch to the "Stage" workspace
    And I visit "/node/add/page"
    And I fill in "WPS Test Title" for "Title"
    And I select "Published" from "Moderation state"
    And I fill in "/wps-test-1" for "URL alias"
    And I press "Save"
    And I am on "/stage/wps-test-1"
    And the response status code should be 200
    And I am an anonymous user
    And I am on "/stage/wps-test-1"
    Then the response status code should be 404
    # Manual cleanup
    And I am on "/user/login"
    And I am logged in as a user with the administrator role
    And I switch to the "Stage" workspace
    And I visit "/admin/content"
    And I click "WPS Test Title"
    And I click "Delete"
    And I press "Delete"

  @b7dde099
  Scenario: The Stage workspace that ships with Lightning has Live as its Upstream
    Given I am logged in as a user with the administrator role
    And I navigate to the "Stage" workspace config form
    # These are actually radio button by the checkbox steps work
    And the "Stage" checkbox should not be checked
    Then the "Live" checkbox should be checked

  @cleanup @e6abc176
  Scenario: Content is not editable after the content's workspace has been moved from unlocked to locked state
    Given I am logged in as a user with the administrator role
    And I set the "Stage" workspace to the "Draft" moderation state
    And I switch to the "Stage" workspace
    And I visit "/node/add/page"
    And I fill in "WPS Test Title" for "Title"
    And I select "Published" from "Moderation state"
    And I fill in "/wps-test-2" for "URL alias"
    And I press "Save"
    And I am on "/stage/wps-test-2"
    And I click "New draft"
    And I fill in "WPS Test Title: edited1" for "Title"
    And I select "Published" from "Moderation state"
    And I press "Save"
    And I should be on "/stage/wps-test-2"
    And I should see "WPS Test Title: edited1"
    And I set the "Stage" workspace to the "Published" moderation state
    And I am on "/stage/wps-test-2"
    And I click "New draft"
    And I should see "Content cannot be modified in a locked workspace"
    Then I should not see the "Save" button
    # Manual cleanup
    And I set the "Stage" workspace to the "Draft" moderation state
    And I visit "/admin/content"
    And I click "WPS Test Title: edited1"
    And I click "Delete"
    And I press "Delete"
    And I switch to the "Live" workspace
    And I visit "/admin/content"
    And I click "WPS Test Title: edited1"
    And I click "Delete"
    And I press "Delete"

  @cleanup @8d5afc40
  Scenario: Content is editable after the content's workspace has been moved from locked to unlocked
    Given I am logged in as a user with the administrator role
    And I set the "Stage" workspace to the "Draft" moderation state
    And I switch to the "Stage" workspace
    And I visit "/node/add/page"
    And I fill in "WPS Test Title 3" for "Title"
    And I select "Published" from "Moderation state"
    And I press "Save"
    And I click "New draft"
    And I fill in "WPS Test Title: edited1" for "Title"
    And I select "Published" from "Moderation state"
    And I press "Save"
    And I should see "WPS Test Title: edited1"
    And I set the "Stage" workspace to the "Published" moderation state
    And I visit "/admin/content"
    And I click "WPS Test Title: edited1"
    And I click "New draft"
    And I should see "Content cannot be modified in a locked workspace"
    And I should not see the "Save" button
    And I set the "Stage" workspace to the "Draft" moderation state
    And I visit "/admin/content"
    And I click "WPS Test Title: edited1"
    And I click "New draft"
    And I fill in "WPS Test Title: edited2" for "Title"
    And I select "Published" from "Moderation state"
    And I press "Save"
    Then I should see "WPS Test Title: edited2"
    # Manual cleanup
    And I visit "/admin/content"
    And I click "WPS Test Title: edited2"
    And I click "Delete"
    And I press "Delete"
    And I switch to the "Live" workspace
    And I visit "/admin/content"
    And I click "WPS Test Title: edited1"
    And I click "Delete"
    And I press "Delete"

  @cleanup @9aa76b6b
  Scenario: Cause and resolve a conflict
    Given I am logged in as a user with the administrator role
    And I switch to the "Live" workspace
    And I visit "/node/add/page"
    And I fill in "WPS Test 4" for "Title"
    And I press "Save"
    And I switch to the "Stage" workspace
    And I pull changes from upstream
    And I should see "Stage has been updated with content from Live"
    And I visit "/admin/content"
    And I click "WPS Test"
    And I click "Edit draft"
    And I fill in "edit-title-0-value" with "WPS Test: Edited"
    And I press "Save"
    And I switch to the "Live" workspace
    And I visit "/admin/content"
    And I click "WPS Test"
    And I click "Edit draft"
    And I fill in "edit-title-0-value" with "WPS Test: Edited"
    And I press "Save"
    And I navigate to the "Stage" workspace config form
    And I select "Published" from "Moderation state"
    And I press "Save"
    And I should see "Pushing changes to Live may result in unexpected behavior or data loss, and cannot be undone"
    And I select the radio button "Yes, if conflicts are found do not replicate to upstream."
    And I press "Save"
    And I switch to the "Stage" workspace
    And I visit "/admin/content"
    And I click "WPS Test: Edited"
    And I click "Delete"
    And I press "Delete"
    And I navigate to the "Stage" workspace config form
    And I select "Published" from "Moderation state"
    And I press "Save"
    And I should not see "Pushing changes to Live may result in unexpected behavior or data loss, and cannot be undone"
    Then I should see "Workspace Stage has been updated and changes were pushed to Live."
    # Manual cleanup
    And I set the "Stage" workspace to the "Draft" moderation state
    And I switch to the "Live" workspace
    And I visit "/admin/content"
    And I click "WPS Test: Edited"
    And I click "Delete"
    And I press "Delete"

  @cleanup @81694bf1
  Scenario: Custom paths are pushed upstream
    Given I am logged in as a user with the administrator role
    And I switch to the "Stage" workspace
    And I visit "/node/add/page"
    And I fill in "WPS Alias Test 2" for "Title"
    And I fill in "/wps-alias-test-2" for "URL alias"
    And I press "Save"
    And I set the "Stage" workspace to the "Published" moderation state
    And I switch to the "Live" workspace
    And I am on "/wps-alias-test-2"
    Then the response status code should be 200
    # Manual Cleanup
    And I click "Delete"
    And I press "Delete"
    And I set the "Stage" workspace to the "Draft" moderation state
    And I switch to the "Stage" workspace
    And I visit "/stage/wps-alias-test-2"
    And I click "Delete"
    And I press "Delete"

  @cleanup @ad3a5240
  Scenario: Custom paths are replicated from upstream on update
    Given I am logged in as a user with the administrator role
    And I visit "/node/add/page"
    And I fill in "WPS Test Test 3" for "Title"
    And I fill in "/wps-alias-test-3" for "URL alias"
    And I press "Save"
    And I switch to the "Stage" workspace
    And I pull changes from upstream
    And I am on "/stage/wps-alias-test-3"
    Then the response status code should be 200
    # Manual cleanup
    And I click "Delete"
    And I press "Delete"
    And I switch to the "Live" workspace
    And I am on "/wps-alias-test-3"
    And I click "Delete"
    And I press "Delete"
