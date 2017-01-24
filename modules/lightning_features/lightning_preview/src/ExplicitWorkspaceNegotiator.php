<?php

namespace Drupal\lightning_preview;

use Drupal\multiversion\Workspace\WorkspaceNegotiatorBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * A workspace negotiator that returns an explicitly set workspace ID.
 */
class ExplicitWorkspaceNegotiator extends WorkspaceNegotiatorBase {

  /**
   * The workspace ID.
   *
   * @var int
   */
  protected $workspaceId;

  /**
   * Sets the workspace ID.
   *
   * @param int $workspace_id
   *   (optional) The workspace ID. If set, the current workspace ID is cleared.
   */
  public function setWorkspace($workspace_id = NULL) {
    $this->workspaceId = $workspace_id;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    return isset($this->workspaceId);
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkspaceId(Request $request) {
    return $this->workspaceId;
  }

}
