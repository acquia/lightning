<?php

namespace Drupal\lightning_core;

use Drupal\Core\Form\FormStateInterface;

/**
 * Alters forms by invoking services tagged with form_decorator.
 */
class FormDecorator {

  /**
   * The decorators to run, keyed by form ID.
   *
   * @var array
   */
  protected $decorators = [];

  /**
   * Adds a form decorator.
   *
   * @param FormDecoratorInterface $decorator
   *   The decorator to add.
   */
  public function addDecorator(FormDecoratorInterface $decorator) {
    foreach ($decorator->getDecoratedForms() as $form_id => $callback) {
      $this->decorators[$form_id][] = [$decorator, $callback];
    }
  }

  /**
   * Alters a form by running all decorators subscribed to it.
   *
   * @param string $form_id
   *   The form ID.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function alterForm($form_id, array &$form, FormStateInterface $form_state) {
    foreach ((array) @$this->decorators[$form_id] as $callback) {
      $callback($form, $form_state);
    }
  }

}
