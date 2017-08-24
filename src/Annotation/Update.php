<?php

namespace Drupal\lightning\Annotation;

use Drupal\Component\Annotation\PluginID;

/**
 * Defines a plugin annotation that contains only a target version number.
 *
 * @Annotation
 */
class Update extends PluginID {

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->getProvider() . ':' . parent::getId();
  }

}
