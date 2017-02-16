<?php

namespace Drupal\lightning_media;

use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Helper for bundle resolvers that deal with source fields.
 */
trait SourceFieldTrait {

  /**
   * Returns the source field definition for a media entity.
   *
   * @param MediaInterface $entity
   *   The media entity.
   *
   * @return \Drupal\Core\Field\FieldConfigInterface
   *   The source field config entity.
   */
  protected function getSourceField(MediaInterface $entity) {
    return $this->getSourceFieldForBundle($entity->bundle->entity);
  }

  /**
   * Returns the source field for a media bundle.
   *
   * @param MediaBundleInterface $bundle
   *   The media bundle entity.
   *
   * @return \Drupal\Core\Field\FieldConfigInterface
   *   The configurable source field entity.
   */
  protected function getSourceFieldForBundle(MediaBundleInterface $bundle) {
    $type_config = $bundle->getType()->getConfiguration();
    if (empty($type_config['source_field'])) {
      return NULL;
    }
    $id = 'media.' . $bundle->id() . '.' . $type_config['source_field'];

    return $this->entityTypeManager()->getStorage('field_config')->load($id);
  }

  /**
   * Returns the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager, duh.
   */
  private function entityTypeManager() {
    return @($this->entityTypeManager ?: \Drupal::entityTypeManager());
  }

}
