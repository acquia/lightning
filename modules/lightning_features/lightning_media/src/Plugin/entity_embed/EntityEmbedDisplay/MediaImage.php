<?php

namespace Drupal\lightning_media\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Don't allow linking directly to the content.
    unset($form['image_link']['#options']['content']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeValues() {
    $field = $this->getItem();
    $label = $field->getEntity()->label();

    // Try to default to the alt and title attributes set on the field item, but
    // fall back to the entity label for both.
    return parent::getAttributeValues() + [
      'alt' =>
        $field->alt ?: $label,
      'title' =>
        $field->title ?: $label,
    ];
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

    $value['target_id'] = $this->getItem()->target_id;

    return $value;
  }

  /**
   * Returns the image field item to use for the embedded entity.
   *
   * @return \Drupal\image\Plugin\Field\FieldType\ImageItem
   *   The image field item.
   */
  protected function getItem() {
    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $this->getEntityFromContext();

    $item = MediaHelper::getSourceField($entity)->first();

    return $item instanceof ImageItem ? $item : $entity->get('thumbnail')->first();
  }

}
