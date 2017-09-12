<?php

namespace Drupal\lightning_api\Exception;

class KeyGenerationException extends \RuntimeException {

  public function __construct($message = "", $code = 0, \Exception $previous = NULL) {
    if (empty($message)) {
      $message = openssl_error_string() ?: 'An internal error occurred';
    }
    parent::__construct($message, $code, $previous);
  }

}
