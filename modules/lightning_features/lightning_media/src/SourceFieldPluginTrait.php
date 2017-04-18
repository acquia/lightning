<?php

namespace Drupal\lightning_media;

use Drupal\media_entity\MediaBundleInterface;

/**
 * Basic implementation of SourceFieldInterface.
 */
trait SourceFieldPluginTrait {

  /**
   * Implements InputMatchInterface::getSourceFieldDefinition().
   */
  public function getSourceFieldDefinition(MediaBundleInterface $bundle) {
    $configuration = $this->getConfiguration();

    if (isset($configuration['source_field'])) {
      $fields = $this->entityFieldManager->getFieldDefinitions(
        $bundle->getEntityType()->getBundleOf(),
        $bundle->id()
      );
      $field = $configuration['source_field'];

      return $fields[$field];
    }
    return NULL;
  }

}
