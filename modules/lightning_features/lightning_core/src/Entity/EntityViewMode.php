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

}
