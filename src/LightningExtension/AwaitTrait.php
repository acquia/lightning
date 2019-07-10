<?php

namespace Acquia\LightningExtension;

@trigger_error(__NAMESPACE__ . '\AwaitTrait is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. Use ' . __NAMESPACE__ . '\Context\AwaitTrait instead. See https://www.drupal.org/node/2105097', E_USER_DEPRECATED);

use Acquia\LightningExtension\Context\AwaitTrait as BaseAwaitTrait;

/**
 * Contains a trait for awaiting various conditions.
 */
trait AwaitTrait {

  use BaseAwaitTrait;

}
