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
    $element = $form_state->get('entity_element');
    $input = $form_state->getUserInput();

    // If we're working with an existing embed, $input['editor_object'] will be
    // set, in which case we don't want to change anything (see ::buildForm()).
    // Otherwise, if the entity being embedded is a media item, see if its type
    // plugin has a preference regarding which display plugin to use.
    if (empty($input['editor_object']) && $entity instanceof MediaInterface) {
      $plugin_definition = $entity->getType()->getPluginDefinition();

      if (isset($plugin_definition['entity_embed_display'])) {
        $element['data-entity-embed-display'] = $plugin_definition['entity_embed_display'];
        $form_state->set('entity_element', $element);
      }
    }
    return parent::buildEmbedStep($form, $form_state);
  }

}
