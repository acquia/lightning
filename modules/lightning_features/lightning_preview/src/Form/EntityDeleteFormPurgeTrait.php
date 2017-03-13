<?php

namespace Drupal\lightning_preview\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;

/**
 * A trait for purging deleted entities when submitting an entity deletion form.
 */
trait EntityDeleteFormPurgeTrait {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $entity = $this->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    if ($storage instanceof ContentEntityStorageInterface) {
      $storage->purge([$entity]);
    }
  }

}
