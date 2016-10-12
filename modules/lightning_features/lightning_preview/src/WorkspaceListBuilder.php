<?php

namespace Drupal\lightning_preview;

use Drupal\Core\Entity\EntityInterface;
use Drupal\workspace\WorkspaceListBuilder as BaseListBuilder;

/**
 * Class WorkspaceListBuilder builds a list of workspaces for the admin page.
 */
class WorkspaceListBuilder extends BaseListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return parent::buildHeader() + [
      'moderation_state' => t('Moderation State'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

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
