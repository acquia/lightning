<?php

namespace Drupal\lightning_media\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_embed\Form\EntityEmbedDialog as BaseEntityEmbedDialog;
use Drupal\media_entity\MediaInterface;

class EntityEmbedDialog extends BaseEntityEmbedDialog {

  /**
   * {@inheritdoc}
   */
  public function buildEmbedStep(array $form, FormStateInterface $form_state) {
    $entity = $form_state->get('entity');
    $entity_element = $form_state->get('entity_element');

    // If the entity being embedded is a media item, see if its handler prefers
    // a certain Entity Embed display plugin.
    if ($entity instanceof MediaInterface) {
      $plugin_definition = $entity->getType()->getPluginDefinition();

      if (isset($plugin_definition['entity_embed_display'])) {
        $entity_element['data-entity-embed-display'] = $plugin_definition['entity_embed_display'];
        $form_state->set('entity_element', $entity_element);
      }
    }
    return parent::buildEmbedStep($form, $form_state);
  }

}
