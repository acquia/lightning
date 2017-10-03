<?php

namespace Drupal\lightning_core\Entity;

use Drupal\Core\Entity\Entity\EntityViewMode as BaseEntityViewMode;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\lightning_core\ConfigEntityDescriptionTrait;

/**
 * Adds description support to entity view modes.
 */
class EntityViewMode extends BaseEntityViewMode implements EntityDescriptionInterface {

  use ConfigEntityDescriptionTrait;

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);

    if (empty($parameters['entity_type_id'])) {
      $parameters['entity_type_id'] = $this->getTargetType();
    }

    return $parameters;
  }

}
