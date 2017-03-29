<?php

namespace Drupal\lightning_core\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a redirect chain in entity forms for bulk entity creation.
 */
trait BulkCreationEntityFormTrait {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $bulk_create = $this->getRequest()->query->get('bulk_create', []);
    if ($bulk_create) {
      $entity_type = $this->getEntity()->getEntityTypeId();
      $id = array_shift($bulk_create);

      $redirect = $this->entityTypeManager
        ->getStorage($entity_type)
        ->load($id)
        ->toUrl('edit-form', [
          'query' => [
            'bulk_create' => $bulk_create,
          ],
        ]);
      $form_state->setRedirectUrl($redirect);
    }
  }

}
