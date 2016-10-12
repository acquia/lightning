<?php

namespace Drupal\lightning_preview;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lightning_preview\Exception\EntityLockedException;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;

/**
 * A service for dealing with workspace locking.
 */
class WorkspaceLock {

  /**
   * The workspace manager.
   *
   * @var WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The replication semaphore.
   *
   * @var ReplicationSemaphore
   */
  protected $semaphore;

  /**
   * The entity type IDs that are never locked.
   *
   * @var string[]
   */
  protected $unlocked = [
    'workspace',
    'replication_log',
  ];

  /**
   * WorkspaceLock constructor.
   *
   * @param WorkspaceManagerInterface $workspace_manager
   *   The workspace manager.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param ReplicationSemaphore $replication_semaphore
   *   The replication semaphore.
   */
  public function __construct(WorkspaceManagerInterface $workspace_manager, EntityTypeManagerInterface $entity_type_manager, ReplicationSemaphore $replication_semaphore) {
    $this->workspaceManager = $workspace_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->semaphore = $replication_semaphore;
  }

  /**
   * Determines if a workspace is locked.
   *
   * @param WorkspaceInterface $workspace
   *   (optional) The workspace to check. Defaults to the active workspace.
   *
   * @return bool
   *   Whether or not the workspace is locked.
   */
  public function isWorkspaceLocked(WorkspaceInterface $workspace = NULL) {
    if (empty($workspace)) {
      $workspace = $this->workspaceManager->getActiveWorkspace();
    }

    if ($workspace->getMachineName() == 'live') {
      return FALSE;
    }

    if ($workspace->hasField('moderation_state')) {
      return in_array(
        $workspace->moderation_state->target_id,
        $workspace->type->entity->getThirdPartySetting('workbench_moderation', 'locked_states', [])
      );
    }
    else {
      return FALSE;
    }
  }

  /**
   * Determines if an entity type is locked.
   *
   * @param string $entity_type
   *   The entity type ID.
   *
   * @return bool
   *   Whether the entity type is locked.
   */
  public function isEntityTypeLocked($entity_type) {
    // Nothing is locked during a replication. Otherwise, certain entity types
    // are never locked.
    if ($this->semaphore->getEvent() || in_array($entity_type, $this->unlocked)) {
      return FALSE;
    }

    $definition = $this->entityTypeManager->getDefinition($entity_type);

    return $definition instanceof ConfigEntityTypeInterface
      ? $this->workspaceManager->getActiveWorkspace()->getMachineName() != 'live'
      : $this->isWorkspaceLocked();
  }

  /**
   * Determines if an entity is locked.
   *
   * @param EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   Whether the entity is locked.
   */
  public function isEntityLocked(EntityInterface $entity) {
    return $this->isEntityTypeLocked(
      $entity->getEntityTypeId()
    );
  }

  /**
   * Asserts that an entity is unlocked.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @throws EntityLockedException
   *   If the entity is locked.
   */
  public function assertEntityUnlocked(EntityInterface $entity) {
    $locked = $this->isEntityLocked($entity);
    if ($locked) {
      throw new EntityLockedException($entity);
    }
  }

}
