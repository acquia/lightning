<?php

namespace Acquia\LightningExtension;

use Drupal\DrupalExtension\Context\MinkContext;

trait AwaitTrait {

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
