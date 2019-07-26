<?php

namespace Acquia\LightningExtension;

@trigger_error(__NAMESPACE__ . '\AwaitTrait is deprecated in lightning:8.x-3.3 and will be removed in lightning:8.x-4.0. Use ' . __NAMESPACE__ . '\Context\AwaitTrait instead. See https://www.drupal.org/node/2105097', E_USER_DEPRECATED);

use Acquia\LightningExtension\Context\AwaitTrait as BaseAwaitTrait;

/**
 * Provides helper methods to wait for various conditions.
 */
trait AwaitTrait {

  use BaseAwaitTrait;

}
