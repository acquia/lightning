<?php

namespace Acquia\LightningExtension;

@trigger_error('\Acquia\LightningExtension\AwaitTrait is deprecated. Use \Acquia\LightningExtension\Context\AwaitTrait instead.', E_USER_DEPRECATED);

use Acquia\LightningExtension\Context\AwaitTrait as BaseAwaitTrait;

trait AwaitTrait {

  use BaseAwaitTrait;

}
