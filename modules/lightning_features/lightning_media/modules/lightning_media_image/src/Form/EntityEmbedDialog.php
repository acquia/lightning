<?php

namespace Drupal\lightning_media_image\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_embed\Form\EntityEmbedDialog as BaseEntityEmbedDialog;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity_image\Plugin\MediaEntity\Type\Image;

class EntityEmbedDialog extends BaseEntityEmbedDialog {

  /**
   * {@inheritdoc}
   */
  public function buildEmbedStep(array $form, FormStateInterface $form_state) {
    $entity = $form_state->get('entity');
    $entity_element = $form_state->get('entity_element');

    // If the entity being embedded is a media item that uses the Image plugin,
    // try to use the media_image display plugin by default.
    if ($entity instanceof MediaInterface && $entity->getType() instanceof Image) {
      $entity_element['data-entity-embed-display'] = 'media_image';
      $form_state->set('entity_element', $entity_element);
    }

    return parent::buildEmbedStep($form, $form_state);
  }

}
