<?php

namespace Drupal\lightning_media;

use Drupal\media\MediaTypeInterface;

/**
 * Basic implementation of SourceFieldInterface.
 *
 * @deprecated in Lightning 2.2.1 and will be removed in Lightning 2.3.0. Use
 * \Drupal\media\MediaSourceInterface::getSourceFieldDefinition() instead.
 */
trait SourceFieldPluginTrait {

  /**
   * Implements InputMatchInterface::getSourceFieldDefinition().
   */
  public function getSourceFieldDefinition(MediaTypeInterface $media_type) {
    return $this->getSourceFieldDefinition($media_type);
  }

}
