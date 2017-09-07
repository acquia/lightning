<?php

namespace Acquia\LightningExtension;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\MinkContext;

trait AwaitTrait {

  /**
   * Waits for AJAX to finish.
   *
   * If the Mink context is unavailable, or the current driver does not support
   * waiting for a JavaScript condition, waits $timeout seconds and returns.
   *
   * @param int $timeout
   *   (optional) How many seconds to wait.
   */
  protected function awaitAjax($timeout = 10) {
    /** @var MinkContext $context */
    $context = $this->getContext(MinkContext::class);

    if ($context) {
      try {
        return $context->iWaitForAjaxToFinish();
      }
      catch (UnsupportedDriverActionException $e) {
        // Fall through to sleep().
      }
    }
    sleep($timeout);
  }

}
