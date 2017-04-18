<?php

namespace Drupal\lightning_media\Exception;

use Drupal\Core\Entity\EntityInterface;

/**
 * Exception thrown if no bundle can be determined from an input value.
 */
class IndeterminateBundleException extends \UnexpectedValueException {

  /**
   * IndeterminateBundleException constructor.
   *
   * @param mixed $value
   *   The input value.
   * @param int $code
   *   (optional) The error code.
   * @param \Exception|NULL $previous
   *   (optional) The previous exception, if any.
   */
  public function __construct($value, $code = 0, \Exception $previous = NULL) {
    $message = sprintf(
      'Could not match any bundles to input: %s',
      $value instanceof EntityInterface ? $value->label() : var_export($value, TRUE)
    );
    parent::__construct($message, $code, $previous);
  }

}
