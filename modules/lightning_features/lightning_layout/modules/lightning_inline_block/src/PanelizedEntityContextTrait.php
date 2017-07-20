<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\ctools\Context\AutomaticContext;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

trait PanelizedEntityContextTrait {

  protected function ensureEntityContext(PanelsDisplayVariant $display, EntityInterface $entity = NULL) {
    $key = '@panelizer.entity_context:entity';

    $contexts = $display->getContexts();
    if (empty($contexts[$key]) && $entity) {
      $contexts[$key] = new AutomaticContext(new ContextDefinition('entity:' . $entity->getEntityTypeId(), NULL, TRUE), $entity);
      $display->setContexts($contexts);
    }
  }

}
