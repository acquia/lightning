<?php

namespace Drupal\lightning_core;

/**
 * Defines the interface for a form decorator.
 */
interface FormDecoratorInterface {

  /**
   * Registers form subscriptions.
   *
   * @return array
   *   An array of methods to call, keyed by form ID.
   */
  public function getDecoratedForms();

}
