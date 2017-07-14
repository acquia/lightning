<?php

namespace Drupal\lightning_inline_block;

use Drupal\lightning_inline_block\Entity\InlineBlockContent;
use Drupal\lightning_workflow\Event\QuickEditAccessEvent;
use Drupal\lightning_workflow\QuickEditAccess as BaseQuickEditAccess;

class QuickEditAccess extends BaseQuickEditAccess {

  /**
   * {@inheritdoc}
   */
  public function onQuickEditAccess(QuickEditAccessEvent $event) {
    $entity = $event->getEntity();

    // If the entity in question is an inline block, invoke the parent method
    // on the host entity.
    if ($entity instanceof InlineBlockContent) {
      /** @var \Drupal\lightning_workflow\Event\QuickEditAccessEvent $new_event */
      $delegate = new QuickEditAccessEvent($entity->getHostEntity());
      parent::onQuickEditAccess($delegate);
      $event->setAccess($delegate->getAccess());
    }
    else {
      parent::onQuickEditAccess($event);
    }
  }

}
