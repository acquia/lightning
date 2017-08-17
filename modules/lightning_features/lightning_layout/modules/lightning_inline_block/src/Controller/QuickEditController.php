<?php

namespace Drupal\lightning_inline_block\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\lightning_inline_block\Ajax\UpdatePanelsIPECommand;
use Drupal\quickedit\QuickEditController as BaseQuickEditController;

class QuickEditController extends BaseQuickEditController {

  /**
   * {@inheritdoc}
   */
  public function entitySave(EntityInterface $entity) {
    return parent::entitySave($entity)
      ->addCommand(
        UpdatePanelsIPECommand::unsaved()
      );
  }

}
