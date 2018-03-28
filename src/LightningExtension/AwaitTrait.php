<?php

namespace Acquia\LightningExtension;

@trigger_error(__NAMESPACE__ . '\AwaitTrait is deprecated. Use ' . __NAMESPACE__ . '\Context\AwaitTrait instead.', E_USER_DEPRECATED);

use Acquia\LightningExtension\Context\AwaitTrait as BaseAwaitTrait;

trait AwaitTrait {

  use BaseAwaitTrait;

}
