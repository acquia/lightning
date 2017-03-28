<?php

namespace Drupal\lightning_core\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a redirect loop in entity forms for bulk entity creation.
 */
trait BulkCreationEntityFormTrait {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $bulk_create = $this->getRequest()->query->get('bulk_create', []);
    if ($bulk_create) {
      $route_match = $this->getRouteMatch();

      $entity_type_id = $this->getEntity()->getEntityTypeId();
      $parameters = $route_match->getRawParameters()->all();
      $parameters[$entity_type_id] = array_shift($bulk_create);

      $options = [];
      if ($bulk_create) {
        $options['query']['bulk_create'] = $bulk_create;
      }

      $form_state->setRedirect(
        $route_match->getRouteName(),
        $parameters,
        $options
      );
    }
  }

}
