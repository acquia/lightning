<?php

namespace Drupal\lightning_media;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for a preview handler.
 *
 * Preview handlers are responsible for altering media entity forms in order
 * to provide and manage a live preview of the entity. Different media types
 * can handle preview differently (depending on the source field type and other
 * factors), so entity forms which will have a preview should use an
 * appropriate implementation of this interface.
 */
interface PreviewHandlerInterface {

  /**
   * Define any extra fields needed to provide a live preview.
   *
   * @param \Drupal\media_entity\MediaBundleInterface|string $bundle
   *   The media bundle, or its ID.
   *
   * @return array
   *   The extra fields, as understood by hook_entity_extra_field_info().
   */
  public function extraFields($bundle);

  /**
   * Alters a media entity form to provide a live preview.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   (optional) The media entity. If not provided, it will be pulled from
   *   the form object.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, EntityInterface $entity = NULL);

}
