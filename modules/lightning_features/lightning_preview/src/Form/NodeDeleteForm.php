<?php

namespace Drupal\lightning_preview\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Form\NodeDeleteForm as BaseNodeDeleteForm;

/**
 * A Multiversion-aware version of NodeDeleteForm.
 */
class NodeDeleteForm extends BaseNodeDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();

    $storage = $this->entityTypeManager->getStorage($entity_type);
    if (method_exists($storage, 'purge')) {
      $storage->purge([$entity]);
    }
  }

}
