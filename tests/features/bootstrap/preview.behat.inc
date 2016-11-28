<?php

/**
 * @file
 * Contains \PreviewSubContext.
 */

use Drupal\workspace\Entity\WorkspacePointer;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions for working with Workspaces and the WPS.
 */
class PreviewSubContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * The Lightning subcontext.
   *
   * @var \LightningSubContext
   */
  protected $lightning;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext('\Drupal\DrupalExtension\Context\MinkContext');
    $this->lightning = $this->getContext('LightningSubContext');
  }

  /**
   * Asserts that a WBM state is Locked.
   *
   * @param string $state
   *   The WBM state to check.
   *
   * @Then the :state state should be locked
   */
  public function assertWbmStateLocked($state) {
    $this->minkContext->assertAtPath('/admin/structure/workbench-moderation/states/' . $state);
    $this->assertSession()->checkboxChecked('edit-lock-workspace');
  }

  /**
   * Asserts that a WBM state is not Locked.
   *
   * @param string $state
   *   The WBM state to check.
   *
   * @Then the :state state should not be locked
   */
  public function assertWbmStateNotLocked($state) {
    $this->minkContext->assertAtPath('/admin/structure/workbench-moderation/states/' . $state);
    $this->assertSession()->checkboxNotChecked('edit-lock-workspace');
  }

  /**
   * Switches to a given workspace.
   *
   * @param string $workspace
   *   The human-readable label of the workspace.
   *
   * @When I switch to the :workspace workspace
   */
  public function switchToWorkspace($workspace) {
    $this->minkContext->clickLink('toolbar-item-workspace-switcher');
    $this->minkContext->pressButton($workspace);
  }

  /**
   * Navigates to a given workspace's configuration page.
   *
   * @When I navigate to the :workspace workspace config form
   */
  public function navigateToWorkspaceConfig($workspace) {
    $workspaces = WorkspacePointer::loadMultiple();
    foreach ($workspaces as $workspace_object) {
      if ($workspace_object->label() == $workspace) {
        $workspace_id = $workspace_object->getWorkspaceId();
        $this->minkContext->assertAtPath('/admin/structure/workspace/' . $workspace_id . '/edit');
        return;
      }
    }
    throw new Exception();
  }

  /**
   * Sets a workspace's moderation state.
   *
   * @param string $workspace
   *   The workspace whose moderation state should be set.
   * @param string $state
   *   The moderation state to set the workspace to.
   *
   * @When I set the :workspace workspace to the :state moderation state
   */
  public function setWorkspaceModerationState($workspace, $state) {
    $this->navigateToWorkspaceConfig($workspace);
    $this->minkContext->selectOption('Moderation state', $state);
    $this->minkContext->pressButton('Save');
  }

  /**
   * Pulls changes from the active workspace's upstream.
   *
   * @When I pull changes from upstream
   */
  public function pullFromUpstream() {
    $this->minkContext->clickLink('Update current workspace from upstream');
    $this->minkContext->pressButton('Confirm');
  }

}
