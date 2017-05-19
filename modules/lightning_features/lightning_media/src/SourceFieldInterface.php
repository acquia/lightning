<?php

namespace Drupal\lightning_media;

use Drupal\media_entity\MediaBundleInterface;

/**
 * An interface for media type plugins that depend on a configured source field.
 */
interface SourceFieldInterface {

  /**
   * Returns the definition of the configured source field.
   *
   * @param \Drupal\media_entity\MediaBundleInterface $bundle
   *   The media bundle that is using this bundle.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|null
   *   The source field definition, or NULL if it does not exist or is not
   *   configured.
   */
  public function getSourceFieldDefinition(MediaBundleInterface $bundle);

}
