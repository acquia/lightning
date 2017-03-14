<?php

namespace Drupal\lightning_core;

/**
 * Provides a third-party settings implementation of EntityDescriptionInterface.
 */
trait ConfigEntityDescriptionTrait {

  /**
   * Implements EntityDescriptionInterface::getDescription().
   */
  public function getDescription() {
    return $this->getThirdPartySetting('lightning_core', 'description');
  }

  /**
   * Implements EntityDescriptionInterface::getDescription().
   */
  public function setDescription($description) {
    return $this->setThirdPartySetting('lightning_core', 'description', (string) $description);
  }

}
