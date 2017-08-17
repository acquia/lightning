<?php

namespace Acquia\LightningExtension;

use Drupal\DrupalExtension\Context\MinkContext;

trait AwaitTrait {

  /**
   * Waits for an AJAX request to finish.
   *
   * @param int $seconds
   *   (optional) How many seconds to wait if the Mink context is unavailable.
   */
  protected function awaitAjax($seconds = 10) {
    /** @var MinkContext $context */
    $context = $this->getContext(MinkContext::class);

    if ($context) {
      $context->iWaitForAjaxToFinish();
    }
    else {
      sleep($seconds);
    }
  }

}
