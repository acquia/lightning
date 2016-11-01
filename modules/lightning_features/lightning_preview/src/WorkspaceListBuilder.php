<?php

namespace Drupal\lightning_preview;

use Drupal\Core\Entity\EntityInterface;
use Drupal\workspace\WorkspaceListBuilder as BaseListBuilder;

/**
 * Builds the administrator-facing list of workspaces.
 */
class WorkspaceListBuilder extends BaseListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();

    // The Status column is weird and confusing from a UX perspective, so get
    // rid of it.
    unset($header['status']);

    return $header + [
      'moderation_state' => $this->t('Moderation State'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

    // The Status column is weird and confusing from a UX perspective, so get
    // rid of it.
    unset($row['status']);

    /** @var \Drupal\multiversion\Entity\WorkspaceInterface $entity */
    if ($entity->hasField('moderation_state') && $entity->getMachineName() != 'live') {
      $row['moderation_state'] = $entity->moderation_state->entity->label();
    }
    else {
      $row['moderation_state'] = t('N/A');
    }
    return $row;
  }

}
