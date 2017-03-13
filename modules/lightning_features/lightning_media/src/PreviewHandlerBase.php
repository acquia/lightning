<?php

namespace Drupal\lightning_media;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for preview handlers.
 *
 * @deprecated in Lightning 2.0.5 and will be removed in Lightning 2.1.0. Media
 * type plugin definitions should add the 'preview' key instead.
 */
abstract class PreviewHandlerBase implements PreviewHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function extraFields() {
    return lightning_media_entity_extra_field_info();
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, EntityInterface $entity = NULL) {
  }

}
