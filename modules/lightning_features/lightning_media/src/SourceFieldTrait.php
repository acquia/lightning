<?php

namespace Drupal\lightning_media;

use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;

/**
 * The definition of a media entity's source field's storage.
 */
trait SourceFieldTrait {

  /**
   * The field_config entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fieldStorage;

  /**
   * Returns the source field definition for a media entity.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
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
   * @param \Drupal\media_entity\MediaBundleInterface $bundle
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

    return $this->fieldStorage->load($id);
  }

}
