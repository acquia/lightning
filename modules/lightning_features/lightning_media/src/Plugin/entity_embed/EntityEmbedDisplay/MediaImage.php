<?php

namespace Drupal\lightning_media\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Access\AccessResult;
use Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\ImageFieldFormatter;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\lightning_media\MediaHelper;

/**
 * Renders a media item's image via the image formatter.
 *
 * If the embedded media item has an image field as its source field, that image
 * is rendered through the image formatter. Otherwise, the media item's
 * thumbnail is used.
 *
 * @EntityEmbedDisplay(
 *   id = "media_image",
 *   label = @Translation("Media Image"),
 *   entity_types = {"media"},
 *   field_type = "image",
 *   provider = "image"
 * )
 */
class MediaImage extends ImageFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterId() {
    return 'image';
  }

  /**
   * {@inheritdoc}
   */
  protected function isValidImage() {
    // This display plugin works for any media entity. And media items always
    // have at least a thumbnail. So, we can bypass this access gate.
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    // The parent method will set the target_type to the entity type being
    // embedded, but we are actually rendering an image (i.e., a file entity).
    return parent::getFieldDefinition()->setSetting('target_type', 'file');
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue() {
    $value = parent::getFieldValue();

    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $this->getEntityFromContext();

    $field = MediaHelper::getSourceField($entity);
    $field = $field instanceof ImageItem ? $field : $entity->get('thumbnail');

    $value['target_id'] = $field->target_id;

    return $value;
  }

}
